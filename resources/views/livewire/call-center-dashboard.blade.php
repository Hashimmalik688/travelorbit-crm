<div wire:poll.30s>
    {{-- Callback reminder modal --}}
    @if($dueCallbacks->isNotEmpty())
        <div x-data="{ open: true }" x-show="open" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4">
            <div class="card w-full max-w-md p-6" x-transition.scale.95>
                <div class="flex items-center gap-3 mb-4">
                    <span class="grid place-items-center w-10 h-10 rounded-full bg-rose-50 text-rose-500 shrink-0 animate-pulse">
                        <x-call-desk.icon name="clock" class="w-5 h-5" />
                    </span>
                    <div>
                        <h3 class="font-semibold text-ink">Callbacks Due Now</h3>
                        <p class="text-xs text-muted">{{ $dueCallbacks->count() }} {{ Str::plural('callback', $dueCallbacks->count()) }} overdue — take action</p>
                    </div>
                </div>
                <ul class="divide-y divide-line/70 max-h-72 overflow-y-auto mb-4">
                    @foreach($dueCallbacks as $f)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-medium text-ink">{{ $f->inquiry->customer->name }}</span>
                                @if($f->notes)<p class="text-sm text-muted truncate">{{ $f->notes }}</p>@endif
                            </div>
                            <span class="text-xs text-rose-600 font-semibold shrink-0">{{ $f->due_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="btn btn-ghost">Dismiss</button>
                    <a href="{{ route('calldesk.callbacks') }}" class="btn btn-primary">View callbacks</a>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-ink">
            {{ $isManager ? 'Team Overview' : 'My Dashboard' }}
        </h2>
        <p class="text-sm text-muted mt-0.5">
            Welcome back, {{ explode(' ', $agent->name)[0] }}.
            {{ $isManager ? 'Monitor all agent performance and team metrics.' : 'Track your leads, calls and conversion performance.' }}
        </p>
    </div>

    @if($isManager)
        {{-- ===== MANAGER DASHBOARD ===== --}}

        {{-- KPI Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5 mb-6">
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-brand-50 text-brand-600 shrink-0"><x-call-desk.icon name="phone" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Total Calls</div><div class="text-2xl font-bold text-ink">{{ $totalCalls }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-blue-50 text-blue-600 shrink-0"><x-call-desk.icon name="inbox" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Open Leads</div><div class="text-2xl font-bold text-ink">{{ $totalOpen }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 shrink-0"><x-call-desk.icon name="check-circle" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Converted</div><div class="text-2xl font-bold text-emerald-600">{{ $totalConverted }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-rose-50 text-rose-600 shrink-0"><x-call-desk.icon name="x-circle" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Lost</div><div class="text-2xl font-bold text-rose-600">{{ $totalLost }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-amber-50 text-amber-600 shrink-0"><x-call-desk.icon name="trending" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Conv. Rate</div><div class="text-2xl font-bold text-ink">{{ $convRate }}%</div></div>
            </div>
        </div>

        {{-- Agent Performance Cards --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Agent Performance</h3>
            @if($perAgent->isEmpty())
                <div class="card px-5 py-12 text-center text-sm text-muted">No calls logged yet.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($perAgent as $row)
                        <div class="card p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="grid place-items-center w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($row['user']->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-semibold text-ink truncate">{{ $row['user']->name }}</div>
                                    <div class="text-xs text-muted">{{ $row['calls'] }} calls · {{ $row['open'] }} open · {{ $row['pending_callbacks'] }} callbacks</div>
                                </div>
                            </div>

                            {{-- Mini stats row --}}
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="text-center rounded-lg bg-emerald-50 py-2">
                                    <div class="text-lg font-bold text-emerald-600">{{ $row['converted'] }}</div>
                                    <div class="text-[10px] text-emerald-600 uppercase font-medium">Converted</div>
                                </div>
                                <div class="text-center rounded-lg bg-rose-50 py-2">
                                    <div class="text-lg font-bold text-rose-600">{{ $row['lost'] }}</div>
                                    <div class="text-[10px] text-rose-600 uppercase font-medium">Lost</div>
                                </div>
                                <div class="text-center rounded-lg bg-brand-50 py-2">
                                    <div class="text-lg font-bold text-brand-600">{{ $row['rate'] }}%</div>
                                    <div class="text-[10px] text-brand-600 uppercase font-medium">Conv.</div>
                                </div>
                            </div>

                            {{-- Conversion rate bar --}}
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-xs text-muted mb-1">
                                    <span>Conversion rate</span>
                                    <span class="font-semibold text-ink">{{ $row['rate'] }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $row['rate'] >= 50 ? 'bg-emerald-500' : ($row['rate'] >= 25 ? 'bg-amber-500' : 'bg-rose-400') }}"
                                         style="width: {{ $row['rate'] }}%"></div>
                                </div>
                            </div>

                            {{-- Disposition mini breakdown --}}
                            @if($row['dispositions']->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                                        @if(($row['dispositions'][$key] ?? 0) > 0)
                                            @php
                                                $color = match($key) {
                                                    'booked', 'booked_by_us', 'already_booked' => 'bg-emerald-100 text-emerald-700',
                                                    'wrong_number', 'booked_elsewhere' => 'bg-rose-100 text-rose-700',
                                                    'no_answer' => 'bg-slate-100 text-slate-600',
                                                    default => 'bg-blue-100 text-blue-700',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium {{ $color }}">
                                                {{ $label }} <span class="font-bold">{{ $row['dispositions'][$key] }}</span>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Two columns: Disposition Breakdown + Recent Activity --}}
        <div class="grid gap-6 xl:grid-cols-2">
            {{-- Disposition Breakdown with visual bars --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-line flex items-center gap-2">
                    <x-call-desk.icon name="chart-bar" class="w-5 h-5 text-slate-400" />
                    <h3 class="font-semibold text-ink">Team Disposition Breakdown</h3>
                    @if($totalDispositioned > 0)
                        <span class="ml-auto text-xs text-muted">{{ $totalDispositioned }} total</span>
                    @endif
                </div>
                @if($dispositionBreakdown->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-muted">No calls logged yet.</div>
                @else
                    <div class="p-5 space-y-3">
                        @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                            @php
                                $count = $dispositionBreakdown[$key] ?? 0;
                                $pct = $totalDispositioned > 0 ? round($count / $totalDispositioned * 100) : 0;
                                $barColor = match($key) {
                                    'booked', 'booked_by_us', 'already_booked' => 'bg-emerald-500',
                                    'wrong_number', 'booked_elsewhere' => 'bg-rose-400',
                                    'no_answer' => 'bg-slate-300',
                                    'will_call_back' => 'bg-amber-400',
                                    default => 'bg-brand-400',
                                };
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-ink tabular-nums">{{ $count }} <span class="text-xs text-muted font-normal">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700 {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Activity Feed --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-line flex items-center gap-2">
                    <x-call-desk.icon name="clock" class="w-5 h-5 text-slate-400" />
                    <h3 class="font-semibold text-ink">Recent Activity</h3>
                </div>
                @if($recentCalls->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-muted">No recent activity.</div>
                @else
                    <ul class="divide-y divide-line/70">
                        @foreach($recentCalls as $call)
                            @php
                                $dispColor = match($call->disposition) {
                                    'booked', 'booked_by_us', 'already_booked' => 'bg-emerald-500',
                                    'wrong_number', 'booked_elsewhere' => 'bg-rose-500',
                                    'no_answer' => 'bg-slate-400',
                                    default => 'bg-brand-500',
                                };
                            @endphp
                            <li class="px-5 py-3 flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full {{ $dispColor }} shrink-0"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-ink text-sm">{{ $call->inquiry?->customer?->name ?? '—' }}</span>
                                        <span class="text-xs text-muted">by {{ $call->user?->name ?? '—' }}</span>
                                    </div>
                                    <div class="text-xs text-muted">{{ \App\Models\CallCenterCall::dispositions()[$call->disposition] ?? $call->disposition }}</div>
                                </div>
                                <span class="text-xs text-muted shrink-0">{{ $call->called_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    @else
        {{-- ===== AGENT DASHBOARD ===== --}}

        {{-- KPI Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5 mb-6">
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-brand-50 text-brand-600 shrink-0"><x-call-desk.icon name="phone" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">My Calls</div><div class="text-2xl font-bold text-ink">{{ $totalCalls }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-blue-50 text-blue-600 shrink-0"><x-call-desk.icon name="inbox" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Open Leads</div><div class="text-2xl font-bold text-ink">{{ $openLeads }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 shrink-0"><x-call-desk.icon name="check-circle" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Booked</div><div class="text-2xl font-bold text-emerald-600">{{ $converted }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-rose-50 text-rose-600 shrink-0"><x-call-desk.icon name="x-circle" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Lost</div><div class="text-2xl font-bold text-rose-600">{{ $lost }}</div></div>
            </div>
            <div class="stat-card">
                <span class="grid place-items-center w-10 h-10 rounded-lg bg-amber-50 text-amber-600 shrink-0"><x-call-desk.icon name="trending" class="w-5 h-5" /></span>
                <div><div class="text-xs text-muted uppercase tracking-wide">Conv. Rate</div><div class="text-2xl font-bold text-ink">{{ $convRate }}%</div></div>
            </div>
        </div>

        {{-- Two columns --}}
        <div class="grid gap-6 xl:grid-cols-2 mb-6">
            {{-- Disposition Breakdown --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-line flex items-center gap-2">
                    <x-call-desk.icon name="chart-bar" class="w-5 h-5 text-slate-400" />
                    <h3 class="font-semibold text-ink">My Disposition Breakdown</h3>
                    @if($totalDispositioned > 0)
                        <span class="ml-auto text-xs text-muted">{{ $totalDispositioned }} total</span>
                    @endif
                </div>
                @if($dispositionCounts->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-muted">No calls logged yet. Start making calls!</div>
                @else
                    <div class="p-5 space-y-3">
                        @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                            @php
                                $count = $dispositionCounts[$key] ?? 0;
                                $pct = $totalDispositioned > 0 ? round($count / $totalDispositioned * 100) : 0;
                                $barColor = match($key) {
                                    'booked', 'booked_by_us', 'already_booked' => 'bg-emerald-500',
                                    'wrong_number', 'booked_elsewhere' => 'bg-rose-400',
                                    'no_answer' => 'bg-slate-300',
                                    'will_call_back' => 'bg-amber-400',
                                    default => 'bg-brand-400',
                                };
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-ink tabular-nums">{{ $count }} <span class="text-xs text-muted font-normal">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700 {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upcoming Callbacks --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-call-desk.icon name="calendar" class="w-5 h-5 text-slate-400" />
                        <h3 class="font-semibold text-ink">My Callbacks</h3>
                    </div>
                    <a href="{{ route('calldesk.callbacks') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">View all →</a>
                </div>
                @if($pendingCallbacks->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <div class="mx-auto w-12 h-12 grid place-items-center rounded-full bg-emerald-50 text-emerald-400 mb-3">
                            <x-call-desk.icon name="check" class="w-6 h-6" />
                        </div>
                        <p class="text-sm font-medium text-ink">All clear!</p>
                        <p class="text-xs text-muted mt-0.5">No pending callbacks.</p>
                    </div>
                @else
                    <ul class="divide-y divide-line/70 max-h-96 overflow-y-auto">
                        @foreach($pendingCallbacks as $f)
                            @php $isOverdue = $f->due_at->isPast(); @endphp
                            <li class="px-5 py-3 flex items-center gap-3">
                                <span class="grid place-items-center w-8 h-8 rounded-full {{ $isOverdue ? 'bg-rose-50 text-rose-500' : 'bg-amber-50 text-amber-500' }} shrink-0">
                                    <x-call-desk.icon name="clock" class="w-4 h-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-ink text-sm">{{ $f->inquiry->customer->name }}</div>
                                    @if($f->notes)<div class="text-xs text-muted truncate">{{ $f->notes }}</div>@endif
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs font-semibold {{ $isOverdue ? 'text-rose-600' : 'text-ink' }}">{{ $f->due_at->format('d M, g:i A') }}</div>
                                    @if($isOverdue)
                                        <div class="text-[10px] text-rose-500 font-medium">OVERDUE</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Recent calls feed --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-line flex items-center gap-2">
                <x-call-desk.icon name="clock" class="w-5 h-5 text-slate-400" />
                <h3 class="font-semibold text-ink">Recent Calls</h3>
            </div>
            @if($recentCalls->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-muted">No calls logged yet.</div>
            @else
                <ul class="divide-y divide-line/70">
                    @foreach($recentCalls as $call)
                        @php
                            $dispColor = match($call->disposition) {
                                'booked', 'booked_by_us', 'already_booked' => 'bg-emerald-500',
                                'wrong_number', 'booked_elsewhere' => 'bg-rose-400',
                                'no_answer' => 'bg-slate-300',
                                default => 'bg-brand-400',
                            };
                            $dispBadge = match($call->disposition) {
                                'booked', 'booked_by_us', 'already_booked' => 'badge-converted',
                                'wrong_number', 'booked_elsewhere' => 'badge-lost',
                                'no_answer' => 'badge-new',
                                default => 'badge-in_progress',
                            };
                        @endphp
                        <li class="px-5 py-3 flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full {{ $dispColor }} shrink-0"></span>
                            <div class="min-w-0 flex-1">
                                <span class="font-medium text-ink text-sm">{{ $call->inquiry?->customer?->name ?? '—' }}</span>
                                @if($call->agent_comment)
                                    <p class="text-xs text-muted truncate">{{ $call->agent_comment }}</p>
                                @endif
                            </div>
                            <span class="badge {{ $dispBadge }}">{{ \App\Models\CallCenterCall::dispositions()[$call->disposition] ?? $call->disposition }}</span>
                            <span class="text-xs text-muted shrink-0">{{ $call->called_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
