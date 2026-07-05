<?php

namespace App\Livewire;

use App\Models\CallCenterFollowup;
use Livewire\Component;

class CallCenterCallbackQueue extends Component
{
    public function markDone($followupId)
    {
        $followup = CallCenterFollowup::find($followupId);
        if ($followup) {
            $followup->update(['status' => 'done']);
            $this->dispatch('notify', message: 'Callback marked done.', type: 'success');
        }
    }

    public function render()
    {
        $followups = CallCenterFollowup::with(['inquiry.customer'])
            ->where('status', 'pending')
            ->orderBy('due_at')
            ->get();

        return view('livewire.call-center-callback-queue', [
            'followups' => $followups,
        ]);
    }
}
