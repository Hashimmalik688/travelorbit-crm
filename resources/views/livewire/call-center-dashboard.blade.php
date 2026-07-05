<div>
    @php
        $dispBadge = function ($key) {
            return match ($key) {
                'booked', 'booked_by_us', 'already_booked' => 'bg-label-success',
                'wrong_number', 'booked_elsewhere' => 'bg-label-danger',
                'no_answer' => 'bg-label-secondary',
                'will_call_back' => 'bg-label-warning',
                default => 'bg-label-primary',
            };
        };
        $dispBar = function ($key) {
            return match ($key) {
                'booked', 'booked_by_us', 'already_booked' => 'bg-success',
                'wrong_number', 'booked_elsewhere' => 'bg-danger',
                'no_answer' => 'bg-secondary',
                'will_call_back' => 'bg-warning',
                default => 'bg-primary',
            };
        };
    @endphp

    @if($dueCallbacks->isNotEmpty())
        <div x-data="{ open: true }" x-show="open" x-transition.opacity class="alert alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
            <i class="ph ph-bell-ringing fs-4"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">Callbacks due now</h6>
                <div class="small mb-2">
                    You have {{ $dueCallbacks->count() }} {{ Str::plural('callback', $dueCallbacks->count()) }} overdue for follow-up.
                </div>
                <ul class="mb-2 ps-3 small">
                    @foreach($dueCallbacks->take(5) as $f)
                        <li>{{ $f->inquiry?->customer?->name ?? '—' }} <span class="text-muted">· {{ $f->due_at->diffForHumans() }}</span></li>
                    @endforeach
                </ul>
                <a href="{{ route('callcenter.callbacks') }}" class="btn btn-sm btn-warning">View callbacks</a>
                <button type="button" class="btn btn-sm btn-link" @click="open = false">Dismiss</button>
            </div>
        </div>
    @endif

    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Call Center</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; Call Center &rsaquo; Overview
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg col-sm-6 animate-in">
            <div class="to-stat accent-indigo">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Calls</div>
                    <div class="to-stat-value">{{ $totalCalls }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-phone-call"></i></div>
            </div>
        </div>
        <div class="col-lg col-sm-6 animate-in">
            <div class="to-stat accent-blue">
                <div class="to-stat-body">
                    <div class="to-stat-label">Open Leads</div>
                    <div class="to-stat-value">{{ $totalOpen }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-tray"></i></div>
            </div>
        </div>
        <div class="col-lg col-sm-6 animate-in">
            <div class="to-stat accent-green">
                <div class="to-stat-body">
                    <div class="to-stat-label">Converted</div>
                    <div class="to-stat-value">{{ $totalConverted }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg col-sm-6 animate-in">
            <div class="to-stat accent-red">
                <div class="to-stat-body">
                    <div class="to-stat-label">Lost</div>
                    <div class="to-stat-value">{{ $totalLost }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-x-circle"></i></div>
            </div>
        </div>
        <div class="col-lg col-sm-6 animate-in">
            <div class="to-stat accent-amber">
                <div class="to-stat-body">
                    <div class="to-stat-label">Conv. Rate</div>
                    <div class="to-stat-value">{{ $convRate }}%</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-trend-up"></i></div>
            </div>
        </div>
    </div>

    {{-- Agent performance --}}
    <div class="mb-4">
        <h6 class="text-uppercase text-muted mb-3" style="font-size:.72rem;letter-spacing:.06em;">Agent Performance</h6>
        @if($perAgent->isEmpty())
            <div class="to-empty">
                <div class="to-empty-icon"><i class="ph ph-users"></i></div>
                <h5>No calls logged yet</h5>
                <p>Once calls are logged, per-agent performance will appear here.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach($perAgent as $row)
                    <div class="col-xl-4 col-md-6 animate-in">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($row['user']->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $row['user']->name }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">{{ $row['calls'] }} calls · {{ $row['open'] }} open · {{ $row['pending_callbacks'] }} callbacks</div>
                                    </div>
                                </div>

                                <div class="row g-2 text-center mb-3">
                                    <div class="col-4">
                                        <div class="rounded p-2 bg-label-success">
                                            <div class="fw-bold">{{ $row['converted'] }}</div>
                                            <div class="text-uppercase" style="font-size:.62rem;">Converted</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="rounded p-2 bg-label-danger">
                                            <div class="fw-bold">{{ $row['lost'] }}</div>
                                            <div class="text-uppercase" style="font-size:.62rem;">Lost</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="rounded p-2 bg-label-primary">
                                            <div class="fw-bold">{{ $row['rate'] }}%</div>
                                            <div class="text-uppercase" style="font-size:.62rem;">Conv.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between text-muted mb-1" style="font-size:.74rem;">
                                    <span>Conversion rate</span>
                                    <span class="fw-semibold text-body">{{ $row['rate'] }}%</span>
                                </div>
                                <div class="progress mb-3" style="height:6px;">
                                    <div class="progress-bar {{ $row['rate'] >= 50 ? 'bg-success' : ($row['rate'] >= 25 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $row['rate'] }}%"></div>
                                </div>

                                @if($row['dispositions']->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                                            @if(($row['dispositions'][$key] ?? 0) > 0)
                                                <span class="badge {{ $dispBadge($key) }}">{{ $label }} {{ $row['dispositions'][$key] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Disposition breakdown + recent activity --}}
    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="ph ph-chart-bar text-muted"></i>
                    <h6 class="mb-0">Team Disposition Breakdown</h6>
                    @if($totalDispositioned > 0)
                        <span class="ms-auto text-muted small">{{ $totalDispositioned }} total</span>
                    @endif
                </div>
                @if($dispositionBreakdown->isEmpty())
                    <div class="to-empty"><p class="mb-0">No calls logged yet.</p></div>
                @else
                    <div class="card-body">
                        @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                            @php
                                $count = $dispositionBreakdown[$key] ?? 0;
                                $pct = $totalDispositioned > 0 ? round($count / $totalDispositioned * 100) : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">{{ $label }}</span>
                                    <span class="fw-semibold">{{ $count }} <span class="text-muted fw-normal">({{ $pct }}%)</span></span>
                                </div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar {{ $dispBar($key) }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="ph ph-clock-counter-clockwise text-muted"></i>
                    <h6 class="mb-0">Recent Activity</h6>
                </div>
                @if($recentCalls->isEmpty())
                    <div class="to-empty"><p class="mb-0">No recent activity.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentCalls as $call)
                            <li class="list-group-item d-flex align-items-center gap-3">
                                <span class="min-w-0 flex-grow-1">
                                    <span class="fw-semibold">{{ $call->inquiry?->customer?->name ?? '—' }}</span>
                                    <span class="text-muted small"> by {{ $call->user?->name ?? '—' }}</span>
                                    <div class="text-muted small">{{ \App\Models\CallCenterCall::dispositions()[$call->disposition] ?? $call->disposition }}</div>
                                </span>
                                <span class="text-muted small text-nowrap">{{ $call->called_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
