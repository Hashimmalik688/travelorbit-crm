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
        $agent = Auth::user();
        $isManager = $agent->isManager();

        // Personal reminder, not a team overview — the modal tells the viewer
        // "you need to call these people back", which is only ever true for
        // callbacks they themselves own, manager or not.
        $dueCallbacks = CallCenterFollowup::with('inquiry.customer')
            ->where('user_id', $agent->id)
            ->where('status', 'pending')
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->get();

        $data = $isManager
            ? $this->managerData()
            : $this->agentData($agent);

        return view('livewire.call-center-dashboard', array_merge($data, [
            'isManager' => $isManager,
            'agent' => $agent,
            'dueCallbacks' => $dueCallbacks,
        ]));
    }

    /** Calls/leads/conversion stats for a single agent, shared by the manager's per-agent cards and the agent's own view. */
    private function statsFor(int $userId): array
    {
        $calls = CallCenterCall::where('user_id', $userId)->where('is_followup', false)->count();
        $converted = CallCenterInquiry::where('user_id', $userId)->where('status', 'converted')->count();
        $lost = CallCenterInquiry::where('user_id', $userId)->where('status', 'lost')->count();
        $open = CallCenterInquiry::where('user_id', $userId)->whereIn('status', ['new', 'in_progress', 'quoted'])->count();
        $rate = ($converted + $lost) > 0 ? round($converted / ($converted + $lost) * 100) : 0;

        $dispositions = CallCenterCall::where('user_id', $userId)
            ->where('is_followup', false)
            ->whereNotNull('disposition')
            ->selectRaw('disposition, count(*) as total')
            ->groupBy('disposition')
            ->pluck('total', 'disposition');

        return compact('calls', 'converted', 'lost', 'open', 'rate', 'dispositions');
    }

    private function managerData(): array
    {
        $totalCalls = CallCenterCall::where('is_followup', false)->count();
        $totalConverted = CallCenterInquiry::where('status', 'converted')->count();
        $totalLost = CallCenterInquiry::where('status', 'lost')->count();
        $totalOpen = CallCenterInquiry::whereIn('status', ['new', 'in_progress', 'quoted'])->count();
        $convRate = ($totalConverted + $totalLost) > 0 ? round($totalConverted / ($totalConverted + $totalLost) * 100) : 0;

        $performerIds = CallCenterInquiry::whereNotNull('user_id')->distinct()->pluck('user_id')
            ->merge(CallCenterCall::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->unique();

        $perAgent = User::whereIn('id', $performerIds)->get()->map(function (User $u) {
            $stats = $this->statsFor($u->id);
            $pendingCb = CallCenterFollowup::where('user_id', $u->id)->where('status', 'pending')->count();

            return [
                'user' => $u,
                'calls' => $stats['calls'],
                'open' => $stats['open'],
                'converted' => $stats['converted'],
                'lost' => $stats['lost'],
                'rate' => $stats['rate'],
                'pending_callbacks' => $pendingCb,
                'dispositions' => $stats['dispositions'],
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

        return compact(
            'totalCalls', 'totalConverted', 'totalLost', 'totalOpen', 'convRate',
            'perAgent', 'dispositionBreakdown', 'totalDispositioned', 'recentCalls',
        );
    }

    private function agentData(User $agent): array
    {
        $stats = $this->statsFor($agent->id);
        $totalCalls = $stats['calls'];
        $converted = $stats['converted'];
        $lost = $stats['lost'];
        $openLeads = $stats['open'];
        $convRate = $stats['rate'];
        $dispositionCounts = $stats['dispositions'];
        $totalDispositioned = $dispositionCounts->sum();

        $pendingCallbacks = CallCenterFollowup::with('inquiry.customer')
            ->where('user_id', $agent->id)
            ->where('status', 'pending')
            ->orderBy('due_at')
            ->get();

        $recentCalls = CallCenterCall::with(['inquiry.customer', 'user'])
            ->where('user_id', $agent->id)
            ->whereNotNull('disposition')
            ->latest('called_at')
            ->take(8)
            ->get();

        return compact(
            'totalCalls', 'converted', 'lost', 'openLeads', 'convRate',
            'dispositionCounts', 'totalDispositioned', 'pendingCallbacks', 'recentCalls',
        );
    }
}
