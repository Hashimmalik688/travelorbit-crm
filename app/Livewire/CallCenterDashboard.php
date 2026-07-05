<?php

namespace App\Livewire;

use App\Models\CallCenterCall;
use App\Models\CallCenterFollowup;
use App\Models\CallCenterInquiry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CallCenterDashboard extends Component
{
    public function render()
    {
        $totalCalls = CallCenterCall::where('is_followup', false)->count();
        $totalLeads = CallCenterInquiry::count();
        $totalConverted = CallCenterInquiry::where('status', 'converted')->count();
        $totalLost = CallCenterInquiry::where('status', 'lost')->count();
        $totalOpen = CallCenterInquiry::whereIn('status', ['new', 'in_progress', 'quoted'])->count();
        $convRate = ($totalConverted + $totalLost) > 0 ? round($totalConverted / ($totalConverted + $totalLost) * 100) : 0;

        $performerIds = CallCenterInquiry::whereNotNull('user_id')->distinct()->pluck('user_id')
            ->merge(CallCenterCall::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->unique();

        $perAgent = User::whereIn('id', $performerIds)->get()->map(function (User $u) {
            $calls = CallCenterCall::where('user_id', $u->id)->where('is_followup', false)->count();
            $conv = CallCenterInquiry::where('user_id', $u->id)->where('status', 'converted')->count();
            $lost = CallCenterInquiry::where('user_id', $u->id)->where('status', 'lost')->count();
            $open = CallCenterInquiry::where('user_id', $u->id)->whereIn('status', ['new', 'in_progress', 'quoted'])->count();
            $rate = ($conv + $lost) > 0 ? round($conv / ($conv + $lost) * 100) : 0;
            $pendingCb = CallCenterFollowup::where('user_id', $u->id)->where('status', 'pending')->count();

            $dispCounts = CallCenterCall::where('user_id', $u->id)
                ->where('is_followup', false)
                ->whereNotNull('disposition')
                ->selectRaw('disposition, count(*) as total')
                ->groupBy('disposition')
                ->pluck('total', 'disposition');

            return [
                'user' => $u,
                'calls' => $calls,
                'open' => $open,
                'converted' => $conv,
                'lost' => $lost,
                'rate' => $rate,
                'pending_callbacks' => $pendingCb,
                'dispositions' => $dispCounts,
            ];
        });

        $dispositionBreakdown = CallCenterCall::where('is_followup', false)
            ->whereNotNull('disposition')
            ->selectRaw('disposition, count(*) as total')
            ->groupBy('disposition')
            ->pluck('total', 'disposition');

        $totalDispositioned = $dispositionBreakdown->sum();

        $recentCalls = CallCenterCall::with(['inquiry.customer', 'user'])
            ->whereNotNull('disposition')
            ->latest('called_at')
            ->take(8)
            ->get();

        $dueCallbacks = CallCenterFollowup::with('inquiry.customer')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->get();

        return view('livewire.call-center-dashboard', [
            'totalCalls' => $totalCalls,
            'totalLeads' => $totalLeads,
            'totalConverted' => $totalConverted,
            'totalLost' => $totalLost,
            'totalOpen' => $totalOpen,
            'convRate' => $convRate,
            'perAgent' => $perAgent,
            'dispositionBreakdown' => $dispositionBreakdown,
            'totalDispositioned' => $totalDispositioned,
            'recentCalls' => $recentCalls,
            'dueCallbacks' => $dueCallbacks,
        ]);
    }
}
