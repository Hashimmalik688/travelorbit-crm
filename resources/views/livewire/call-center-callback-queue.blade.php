<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Callbacks</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="{{ route('callcenter.dashboard') }}">Call Center</a> &rsaquo; Callbacks
            </div>
        </div>
    </div>

    @php
        $overdue = $followups->filter(fn ($f) => $f->due_at->isPast());
        $upcoming = $followups->reject(fn ($f) => $f->due_at->isPast());
    @endphp

    @if($followups->isEmpty())
        <div class="card">
            <div class="to-empty">
                <div class="to-empty-icon"><i class="ph ph-check-circle"></i></div>
                <h5>All caught up</h5>
                <p>No pending callbacks in the queue.</p>
            </div>
        </div>
    @else
        @php
            $groups = [
                ['title' => 'Overdue', 'items' => $overdue, 'badge' => 'bg-label-danger'],
                ['title' => 'Upcoming', 'items' => $upcoming, 'badge' => 'bg-label-primary'],
            ];
        @endphp

        @foreach($groups as $group)
            @if($group['items']->isNotEmpty())
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h6 class="mb-0">{{ $group['title'] }}</h6>
                        <span class="badge {{ $group['badge'] }}">{{ $group['items']->count() }}</span>
                    </div>

                    <div class="card">
                        <ul class="list-group list-group-flush">
                            @foreach($group['items'] as $f)
                                <li class="list-group-item d-flex align-items-center gap-3" wire:key="cb-{{ $f->id }}">
                                    <span class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded-circle {{ $f->due_at->isPast() ? 'bg-label-danger' : 'bg-label-primary' }}">
                                            <i class="ph ph-clock"></i>
                                        </span>
                                    </span>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-medium">{{ $f->inquiry->customer->name }}</div>
                                        <div class="text-muted small">
                                            {{ $f->inquiry->customer->phone }}
                                            @if($f->notes)<span> · {{ $f->notes }}</span>@endif
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <div class="fw-semibold small {{ $f->due_at->isPast() ? 'text-danger' : '' }}">{{ $f->due_at->format('g:i A') }}</div>
                                        <div class="text-muted small">{{ $f->due_at->format('d M') }} · {{ $f->due_at->diffForHumans() }}</div>
                                    </div>
                                    <button type="button" wire:click="markDone({{ $f->id }})" class="btn btn-sm btn-outline-success flex-shrink-0">
                                        <i class="ph ph-check me-1"></i> Done
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
