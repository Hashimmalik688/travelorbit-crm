<?php

namespace App\Livewire;

use App\Models\CallCenterCall;
use App\Models\CallCenterFollowup;
use App\Models\CallCenterInquiry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CallCenterInquiryList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterDisposition = '';
    public $filterMonth = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    public $selectedId = null;

    // Follow-up state
    public $followUpCallId = null;
    public $followUpInquiryId = null;
    public $fuDisposition = '';
    public $fuCallerComment = '';
    public $fuAgentComment = '';
    public $fuMisNumber = '';

    // Callback scheduling (independent of disposition)
    public $fuScheduleCallback = false;
    public $fuCallbackAt = '';

    public function updatedSearch()   { $this->resetPage(); }
    public function updatedFilterType() { $this->resetPage(); }
    public function updatedFilterDisposition() { $this->resetPage(); }

    public function updatedFilterMonth($value)
    {
        if ($value) {
            $this->filterDateFrom = '';
            $this->filterDateTo = '';
        }
        $this->resetPage();
    }

    public function updatedFilterDateFrom()
    {
        if ($this->filterDateFrom) $this->filterMonth = '';
        $this->resetPage();
    }

    public function updatedFilterDateTo()
    {
        if ($this->filterDateTo) $this->filterMonth = '';
        $this->resetPage();
    }

    public function clearDates()
    {
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->filterMonth = '';
        $this->resetPage();
    }

    public function open($id)
    {
        $inquiry = CallCenterInquiry::findOrFail($id);
        abort_unless(Auth::user()->canViewAllData() || $inquiry->user_id === Auth::id(), 403);

        $this->selectedId = $id;
        $this->resetFollowUp();
    }

    public function close()
    {
        $this->selectedId = null;
        $this->resetFollowUp();
    }

    public function startFollowUp($inquiryId)
    {
        $inquiry = CallCenterInquiry::findOrFail($inquiryId);
        abort_unless(Auth::user()->canViewAllData() || $inquiry->user_id === Auth::id(), 403);

        $call = CallCenterCall::create([
            'inquiry_id' => $inquiryId,
            'user_id' => Auth::id(),
            'direction' => 'inbound',
            'disposition' => null,
            'is_followup' => true,
            'called_at' => now(),
        ]);

        $this->followUpCallId = $call->id;
        $this->followUpInquiryId = $inquiryId;

        $this->dispatch('notify', message: 'Follow-up call started.', type: 'success');
    }

    public function saveFollowUp()
    {
        $rules = [
            'fuDisposition' => 'required|in:' . implode(',', array_keys(CallCenterCall::dispositions())),
        ];

        if (CallCenterCall::isBookedDisposition($this->fuDisposition)) {
            $rules['fuMisNumber'] = 'required|string|max:255';
        }
        if ($this->fuScheduleCallback) {
            $rules['fuCallbackAt'] = 'required|date';
        }

        $this->validate($rules);

        $call = CallCenterCall::findOrFail($this->followUpCallId);
        abort_unless(Auth::user()->canViewAllData() || $call->user_id === Auth::id(), 403);

        DB::transaction(function () use ($call) {
            $call->update([
                'disposition' => $this->fuDisposition,
                'caller_comment' => $this->fuCallerComment,
                'agent_comment' => $this->fuAgentComment,
            ]);

            $inquiry = $call->inquiry;
            $inquiry->update([
                'status' => CallCenterCall::statusFor($this->fuDisposition),
                'last_disposition' => $this->fuDisposition,
                'mis_number' => CallCenterCall::isBookedDisposition($this->fuDisposition) ? $this->fuMisNumber : $inquiry->mis_number,
            ]);

            if ($this->fuScheduleCallback && $this->fuCallbackAt) {
                CallCenterFollowup::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => Auth::id(),
                    'due_at' => $this->fuCallbackAt,
                    'status' => 'pending',
                    'notes' => $this->fuAgentComment ?: $this->fuCallerComment,
                ]);
            }
        });

        $this->dispatch('notify', message: 'Follow-up call logged.', type: 'success');
        $this->resetFollowUp();
    }

    public function cancelFollowUp()
    {
        if ($this->followUpCallId) {
            CallCenterCall::where('id', $this->followUpCallId)
                ->whereNull('disposition')
                ->when(! Auth::user()->canViewAllData(), fn ($q) => $q->where('user_id', Auth::id()))
                ->delete();
        }
        $this->resetFollowUp();
    }

    private function resetFollowUp()
    {
        $this->followUpCallId = null;
        $this->followUpInquiryId = null;
        $this->fuDisposition = '';
        $this->fuCallerComment = '';
        $this->fuAgentComment = '';
        $this->fuMisNumber = '';
        $this->fuScheduleCallback = false;
        $this->fuCallbackAt = '';
    }

    public function render()
    {
        $user = Auth::user();
        $isManager = $user->canViewAllData();

        $query = CallCenterInquiry::with(['customer', 'user'])
            ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
            ->latest();

        if ($this->search !== '') {
            $term = trim($this->search);
            $query->whereHas('customer', function ($q) use ($term) {
                $q->where('name', 'ILIKE', "%{$term}%")
                    ->orWhere('phone', 'ILIKE', "%{$term}%");
            });
        }
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }
        if ($this->filterDisposition) {
            $query->where('last_disposition', $this->filterDisposition);
        }

        if ($this->filterMonth) {
            $query->whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$this->filterMonth]);
        } elseif ($this->filterDateFrom || $this->filterDateTo) {
            if ($this->filterDateFrom) {
                $query->whereDate('created_at', '>=', $this->filterDateFrom);
            }
            if ($this->filterDateTo) {
                $query->whereDate('created_at', '<=', $this->filterDateTo);
            }
        }

        $selected = null;
        if ($this->selectedId) {
            $selected = CallCenterInquiry::with(['customer', 'user', 'calls.user', 'followups'])
                ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
                ->whereKey($this->selectedId)
                ->first();
        }

        return view('livewire.call-center-inquiry-list', [
            'inquiries' => $query->paginate(15),
            'selected' => $selected,
            'isManager' => $isManager,
        ]);
    }
}
