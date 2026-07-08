<?php

namespace App\Livewire;

use App\Models\CallCenterCall;
use App\Models\CallCenterCustomer;
use App\Models\CallCenterFollowup;
use App\Models\CallCenterInquiry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CallCenterNewCall extends Component
{
    // Step 1: customer lookup
    public $phoneSearch = '';
    public $matchedCustomerId = null;
    public $custName = '';
    public $custPhone = '';
    public $custCity = '';
    public $customerInquiries = [];

    // New vs. follow-up choice
    public $mode = null; // 'new' | 'existing'
    public $selectedInquiryId = null;

    // Step 2: inquiry (only used when mode === 'new')
    public $type = '';
    public $source = '';
    public $adults = 1;
    public $children = 0;
    public $infants = 0;
    public $details = [];

    // Active call, set once "Add Call" has persisted a row
    public $activeCallId = null;
    public $activeInquiryId = null;
    public $activeInquiryLabel = '';

    // Step 3: disposition + comments
    public $disposition = '';
    public $callerComment = '';
    public $agentComment = '';
    public $misNumber = '';

    // Callback scheduling (independent of disposition)
    public $scheduleCallback = false;
    public $callbackAt = '';

    public function searchPhone()
    {
        $customer = CallCenterCustomer::findByPhone(trim($this->phoneSearch), Auth::user());

        if ($customer) {
            $this->matchedCustomerId = $customer->id;
            $this->custName = $customer->name;
            $this->custPhone = $customer->phone;
            $this->custCity = $customer->city;
            $this->customerInquiries = $customer->inquiries()->latest()->get()->toArray();
        } else {
            $this->matchedCustomerId = null;
            $this->custPhone = $this->phoneSearch;
            $this->custName = '';
            $this->custCity = '';
            $this->customerInquiries = [];
        }

        $this->type = '';
        $this->source = '';
        $this->details = [];
        $this->selectedInquiryId = null;

        $this->mode = empty($this->customerInquiries) ? 'new' : null;
    }

    public function chooseNew()
    {
        $this->mode = 'new';
        $this->selectedInquiryId = null;
        $this->type = '';
        $this->source = '';
        $this->adults = 1;
        $this->children = 0;
        $this->infants = 0;
        $this->details = [];
    }

    public function chooseExisting($inquiryId)
    {
        $this->mode = 'existing';
        $this->selectedInquiryId = $inquiryId;
    }

    public function backToPicker()
    {
        $this->mode = null;
        $this->selectedInquiryId = null;
    }

    public function updatedType($value)
    {
        $this->details = [];
        foreach (CallCenterInquiry::detailFieldsFor($value) as $field) {
            $this->details[$field[0]] = '';
        }
    }

    public function dispositions()
    {
        return CallCenterCall::dispositions();
    }

    public function sources()
    {
        return CallCenterInquiry::sources();
    }

    public function addCall()
    {
        $user = Auth::user();
        $existingInquiry = null;
        $matchedCustomer = null;

        if ($this->mode === 'existing') {
            $this->validate(['selectedInquiryId' => 'required|exists:callcenter_inquiries,id']);
            $existingInquiry = CallCenterInquiry::findOrFail($this->selectedInquiryId);
            abort_unless($user->isManager() || $existingInquiry->user_id === $user->id, 403);
        } else {
            $this->validate([
                'custName' => 'required|string|max:255',
                'custPhone' => 'required|string|max:50',
                'type' => 'required|in:flight,hotel,holiday,umrah,visa',
                'source' => 'required',
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'infants' => 'nullable|integer|min:0',
            ]);

            if ($this->matchedCustomerId) {
                $matchedCustomer = CallCenterCustomer::findOrFail($this->matchedCustomerId);
                abort_unless($user->isManager() || $matchedCustomer->user_id === $user->id, 403);
            }
        }

        $userId = $user->id;

        DB::transaction(function () use ($userId, $existingInquiry, $matchedCustomer) {
            if ($this->mode === 'existing') {
                $inquiry = $existingInquiry;
            } else {
                $customer = $matchedCustomer ?? CallCenterCustomer::create([
                        'user_id' => $userId,
                        'name' => $this->custName,
                        'phone' => $this->custPhone,
                        'city' => $this->custCity,
                    ]);

                $inquiry = CallCenterInquiry::create([
                    'customer_id' => $customer->id,
                    'user_id' => $userId,
                    'type' => $this->type,
                    'source' => $this->source,
                    'adults' => $this->adults,
                    'children' => $this->children ?: 0,
                    'infants' => $this->infants ?: 0,
                    'status' => 'new',
                    'details' => $this->details,
                ]);
            }

            $call = CallCenterCall::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => $userId,
                'direction' => 'inbound',
                'disposition' => null,
                'called_at' => now(),
            ]);

            $this->activeCallId = $call->id;
            $this->activeInquiryId = $inquiry->id;
            $this->activeInquiryLabel = "#{$inquiry->id} · {$inquiry->customer->name}";
        });

        $this->dispatch('notify', message: "Call started for {$this->activeInquiryLabel}.", type: 'success');
    }

    public function saveCall()
    {
        $rules = [
            'disposition' => 'required|in:' . implode(',', array_keys(CallCenterCall::dispositions())),
        ];

        if (CallCenterCall::isBookedDisposition($this->disposition)) {
            $rules['misNumber'] = 'required|string|max:255';
        }
        if ($this->scheduleCallback) {
            $rules['callbackAt'] = 'required|date';
        }

        $this->validate($rules);

        $user = Auth::user();
        $userId = $user->id;

        $call = CallCenterCall::findOrFail($this->activeCallId);
        abort_unless($user->isManager() || $call->user_id === $userId, 403);

        DB::transaction(function () use ($userId, $call) {
            $call->update([
                'disposition' => $this->disposition,
                'caller_comment' => $this->callerComment,
                'agent_comment' => $this->agentComment,
            ]);

            $inquiry = $call->inquiry;
            $inquiry->update([
                'status' => CallCenterCall::statusFor($this->disposition),
                'last_disposition' => $this->disposition,
                'mis_number' => CallCenterCall::isBookedDisposition($this->disposition) ? $this->misNumber : $inquiry->mis_number,
            ]);

            if ($this->scheduleCallback && $this->callbackAt) {
                CallCenterFollowup::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => $userId,
                    'due_at' => $this->callbackAt,
                    'status' => 'pending',
                    'notes' => $this->agentComment ?: $this->callerComment,
                ]);
            }
        });

        $this->dispatch('notify', message: "Call logged for {$this->activeInquiryLabel}.", type: 'success');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'phoneSearch', 'matchedCustomerId', 'custName', 'custPhone', 'custCity', 'customerInquiries',
            'mode', 'selectedInquiryId',
            'type', 'source', 'details',
            'activeCallId', 'activeInquiryId', 'activeInquiryLabel',
            'disposition', 'callerComment', 'agentComment', 'callbackAt', 'misNumber',
            'scheduleCallback',
        ]);
        $this->adults = 1;
        $this->children = 0;
        $this->infants = 0;
    }

    public function render()
    {
        return view('livewire.call-center-new-call');
    }
}
