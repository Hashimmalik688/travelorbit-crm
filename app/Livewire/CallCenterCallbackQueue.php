<?php

namespace App\Livewire;

use App\Models\CallCenterFollowup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CallCenterCallbackQueue extends Component
{
    public function markDone($followupId)
    {
        $user = Auth::user();

        $followup = CallCenterFollowup::where('id', $followupId)
            ->when(! $user->canViewAllData(), fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if ($followup) {
            $followup->update(['status' => 'done']);
            $this->dispatch('notify', message: 'Callback marked done.', type: 'success');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isManager = $user->canViewAllData();

        $followups = CallCenterFollowup::with(['inquiry.customer', 'user'])
            ->where('status', 'pending')
            ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('due_at')
            ->get();

        return view('livewire.call-center-callback-queue', [
            'followups' => $followups,
            'isManager' => $isManager,
        ]);
    }
}
