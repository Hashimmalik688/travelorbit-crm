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
        $typeIcon = fn ($t) => ['flight' => 'ph-airplane-tilt', 'hotel' => 'ph-building', 'holiday' => 'ph-map-pin', 'umrah' => 'ph-moon-stars', 'visa' => 'ph-passport'][$t] ?? 'ph-tray';
    @endphp

    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Inquiries</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="{{ route('callcenter.dashboard') }}">Call Center</a> &rsaquo; Inquiries
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="to-filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control" placeholder="Customer name or phone…" wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <x-styled-select modelName="filterType" placeholder="All types" :live="true" :options="[
                    ['value' => 'flight', 'label' => 'Flight'],
                    ['value' => 'hotel', 'label' => 'Hotel'],
                    ['value' => 'holiday', 'label' => 'Holiday'],
                    ['value' => 'umrah', 'label' => 'Umrah'],
                    ['value' => 'visa', 'label' => 'Visa'],
                ]" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Disposition</label>
                <x-styled-select modelName="filterDisposition" placeholder="All dispositions" :live="true" :options="collect(\App\Models\CallCenterCall::dispositions())->map(fn($label, $key) => ['value' => $key, 'label' => $label])->values()->all()" />
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" class="form-control" wire:model.live="filterDateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" class="form-control" wire:model.live="filterDateTo">
            </div>
            @if($filterDateFrom || $filterDateTo || $filterType || $filterDisposition || $search)
                <div class="col-md-12">
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearDates">
                        <i class="ph ph-x me-1"></i> Clear filters
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Agent</th>
                        <th>Pax</th>
                        <th>Disposition</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inquiries as $inq)
                        <tr wire:key="inq-{{ $inq->id }}" wire:click="open({{ $inq->id }})" style="cursor:pointer;">
                            <td>
                                <div class="fw-semibold">{{ $inq->customer->name }}</div>
                                <div class="text-muted small">{{ $inq->customer->phone }}</div>
                            </td>
                            <td class="text-capitalize">
                                <i class="ph {{ $typeIcon($inq->type) }} text-muted me-1"></i>{{ $inq->type }}
                            </td>
                            <td>{{ $inq->user?->name ?? '—' }}</td>
                            <td>{{ $inq->totalPax() }}</td>
                            <td>
                                @if($inq->last_disposition)
                                    <span class="badge {{ $dispBadge($inq->last_disposition) }}">{{ \App\Models\CallCenterCall::dispositions()[$inq->last_disposition] ?? $inq->last_disposition }}</span>
                                @else
                                    <span class="badge bg-label-info">New</span>
                                @endif
                            </td>
                            <td class="text-muted text-nowrap">{{ $inq->created_at->format('d M, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-tray"></i></div>
                                    <h5>No inquiries match your filters</h5>
                                    <p>Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $inquiries->links() }}
        </div>
    </div>

    {{-- Detail modal --}}
    @if($selected)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);" wire:click="close" wire:key="drawer-{{ $selected->id }}">
            <div style="background:#fff;border-radius:18px;width:100%;max-width:620px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 32px 96px rgba(0,0,0,.35);overflow:hidden;" wire:click.stop="">
                <div style="padding:18px 24px;background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0" style="font-size:.95rem;">Inquiry #{{ $selected->id }}</h5>
                        @if($selected->last_disposition)
                            <span class="badge bg-white text-dark">{{ \App\Models\CallCenterCall::dispositions()[$selected->last_disposition] ?? $selected->last_disposition }}</span>
                        @else
                            <span class="badge bg-white text-dark">New</span>
                        @endif
                    </div>
                    <button type="button" wire:click="close" style="background:rgba(255,255,255,.18);color:#fff;border:none;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;">✕</button>
                </div>

                <div style="overflow-y:auto;padding:22px 24px;">
                    {{-- Customer --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($selected->customer->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $selected->customer->name }}</div>
                            <div class="text-muted small">{{ $selected->customer->phone }}{{ $selected->customer->city ? ' · '.$selected->customer->city : '' }}</div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded px-3 py-2">
                                <div class="text-muted small">Type</div>
                                <div class="fw-medium text-capitalize">{{ $selected->type }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded px-3 py-2">
                                <div class="text-muted small">Passengers</div>
                                <div class="fw-medium">{{ $selected->totalPax() }} ({{ $selected->adults }}A · {{ $selected->children }}C · {{ $selected->infants }}I)</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded px-3 py-2">
                                <div class="text-muted small">Source</div>
                                <div class="fw-medium">{{ \App\Models\CallCenterInquiry::sources()[$selected->source] ?? $selected->source }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded px-3 py-2">
                                <div class="text-muted small">Agent</div>
                                <div class="fw-medium">{{ $selected->user?->name ?? '—' }}</div>
                            </div>
                        </div>
                        @if($selected->mis_number)
                            <div class="col-12">
                                <div class="border rounded px-3 py-2 bg-label-success">
                                    <div class="small">MIS #</div>
                                    <div class="fw-medium">{{ $selected->mis_number }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Trip details --}}
                    @php $fields = \App\Models\CallCenterInquiry::detailFieldsFor($selected->type); @endphp
                    @if(count($fields))
                        <div class="mb-3">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Trip details</h6>
                            <div class="border rounded overflow-hidden">
                                @foreach($fields as [$key, $label, $t])
                                    <div class="d-flex justify-content-between px-3 py-2 small {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <span class="text-muted">{{ $label }}</span>
                                        <span class="fw-medium">{{ \App\Models\CallCenterInquiry::detailValueLabel($key, $selected->detail($key)) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Call history --}}
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Call history</h6>
                        @if($selected->calls->isEmpty())
                            <p class="text-muted small mb-0">No calls logged.</p>
                        @else
                            <ul class="list-group list-group-flush border rounded">
                                @foreach($selected->calls as $call)
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="fw-medium small">
                                                {{ $call->disposition ? (\App\Models\CallCenterCall::dispositions()[$call->disposition] ?? $call->disposition) : 'In progress' }}
                                            </span>
                                            @if(! $call->disposition)
                                                <span class="badge bg-label-warning">open</span>
                                            @endif
                                        </div>
                                        @if($call->caller_comment)
                                            <p class="small mb-0 mt-1"><span class="text-muted">Caller:</span> {{ $call->caller_comment }}</p>
                                        @endif
                                        @if($call->agent_comment)
                                            <p class="small mb-0 mt-1"><span class="text-muted">Agent:</span> {{ $call->agent_comment }}</p>
                                        @endif
                                        <div class="text-muted small mt-1">
                                            {{ $call->called_at?->format('d M Y, g:i A') }}{{ $call->user ? ' · '.$call->user->name : '' }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Scheduled callbacks --}}
                    @if($selected->followups->isNotEmpty())
                        <div class="mb-3">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Scheduled callbacks</h6>
                            @foreach($selected->followups as $f)
                                <div class="d-flex align-items-center gap-2 small border rounded px-3 py-2 mb-1">
                                    <i class="ph {{ $f->status === 'done' ? 'ph-check-circle text-success' : 'ph-clock text-warning' }}"></i>
                                    <span>{{ $f->due_at->format('d M, g:i A') }}</span>
                                    <span class="badge {{ $f->status === 'done' ? 'bg-label-success' : 'bg-label-warning' }} ms-auto">{{ $f->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Follow-up action --}}
                    @if($followUpCallId && $followUpInquiryId === $selected->id)
                        <div class="border rounded p-3 bg-label-primary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Log follow-up call</h6>
                                <button type="button" class="btn btn-sm btn-link" wire:click="cancelFollowUp">Cancel</button>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Disposition</label>
                                <select wire:model.live="fuDisposition" class="form-select">
                                    <option value="">Select disposition…</option>
                                    @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('fuDisposition') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Caller Comment</label>
                                    <textarea wire:model="fuCallerComment" class="form-control" rows="2" placeholder="What the caller said…"></textarea>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Agent Comments</label>
                                    <textarea wire:model="fuAgentComment" class="form-control" rows="2" placeholder="Your notes / next steps…"></textarea>
                                </div>
                            </div>

                            @if(\App\Models\CallCenterCall::isBookedDisposition($fuDisposition))
                                <div class="mb-2">
                                    <label class="form-label">MIS #</label>
                                    <input type="text" wire:model="fuMisNumber" class="form-control" placeholder="Booking software reference">
                                    @error('fuMisNumber') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="border rounded p-2 mb-2 bg-white">
                                <div class="form-check">
                                    <input type="checkbox" wire:model.live="fuScheduleCallback" class="form-check-input" id="fuScheduleCallback">
                                    <label class="form-check-label" for="fuScheduleCallback">Schedule a callback</label>
                                </div>
                                @if($fuScheduleCallback)
                                    <div class="mt-2">
                                        <label class="form-label">Callback date &amp; time</label>
                                        <input type="datetime-local" wire:model="fuCallbackAt" class="form-control">
                                        @error('fuCallbackAt') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm" wire:click="saveFollowUp" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveFollowUp">Save call</span>
                                    <span wire:loading wire:target="saveFollowUp">Saving…</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <button type="button" class="btn btn-primary w-100" wire:click="startFollowUp({{ $selected->id }})">
                            <i class="ph ph-phone-call me-1"></i> Follow Up
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
