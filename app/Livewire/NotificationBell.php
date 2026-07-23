<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\CallCenterFollowup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggle(): void { $this->open = !$this->open; }
    public function close(): void  { $this->open = false; }

    public function render()
    {
        $user  = Auth::user();
        $items = collect();

        if ($user->hasPermission('issuance.access')) {
            $n = Booking::where('booking_status','issuance_queue')->count();
            if ($n > 0) $items->push(['icon'=>'ph-ticket','color'=>'#D97706','bg'=>'rgba(217,119,6,.08)','title'=>"{$n} booking".($n>1?'s':'').' in Issuance Queue','sub'=>'Awaiting ticket order','url'=>route('issuance.dashboard')]);
        }

        if ($user->hasPermission('accounts.access')) {
            $n = Booking::where('booking_status','ticket_in_process')->count();
            if ($n > 0) $items->push(['icon'=>'ph-receipt','color'=>'#0EA5E9','bg'=>'rgba(14,165,233,.08)','title'=>"{$n} booking".($n>1?'s':'').' ready to invoice','sub'=>'Ticket in Process','url'=>route('payments')]);
        }

        if ($user->hasPermission('accounts.access')) {
            $n = BookingPayment::where('balance_remaining','>',0)->whereNotNull('due_date')->where('due_date','<',now())->count();
            if ($n > 0) $items->push(['icon'=>'ph-warning-circle','color'=>'#DC2626','bg'=>'rgba(220,38,38,.08)','title'=>"{$n} overdue payment".($n>1?'s':''),'sub'=>'Past balance due date','url'=>route('payments')]);
        }

        if ($user->role === 'agent') {
            $n = Booking::where('user_id',$user->id)->where('booking_status','pending')->count();
            if ($n > 0) $items->push(['icon'=>'ph-clock','color'=>'#332E9E','bg'=>'rgba(51,46,158,.08)','title'=>"{$n} pending booking".($n>1?'s':''),'sub'=>'Not yet confirmed','url'=>route('agent.dashboard')]);
        }

        $n = CallCenterFollowup::where('status','pending')->where('due_at','<',now())
            ->when(! $user->canViewAllData(), fn ($q) => $q->where('user_id', $user->id))
            ->count();
        if ($n > 0) $items->push(['icon'=>'ph-phone-x','color'=>'#DC2626','bg'=>'rgba(220,38,38,.08)','title'=>"{$n} overdue callback".($n>1?'s':''),'sub'=>'Call Desk','url'=>route('calldesk.callbacks')]);

        return view('livewire.notification-bell', ['notifications' => $items]);
    }
}
