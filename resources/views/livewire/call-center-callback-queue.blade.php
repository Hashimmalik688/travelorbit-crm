<div wire:poll.30s>
    @php
        $overdue = $followups->filter(fn ($f) => $f->due_at->isPast());
        $upcoming = $followups->reject(fn ($f) => $f->due_at->isPast());
    @endphp

    @if($followups->isEmpty())
        <div class="card py-16 text-center">
            <div class="mx-auto w-14 h-14 grid place-items-center rounded-full bg-emerald-50 text-emerald-500 mb-4">
                <x-call-desk.icon name="check-circle" class="w-7 h-7" />
            </div>
            <h3 class="font-semibold text-ink">All caught up</h3>
            <p class="text-sm text-muted mt-1">No pending callbacks in your queue.</p>
        </div>
    @else
        @php
            $groups = [
                ['title' => 'Overdue', 'items' => $overdue, 'accent' => 'text-rose-600', 'dot' => 'bg-rose-500'],
                ['title' => 'Upcoming', 'items' => $upcoming, 'accent' => 'text-ink', 'dot' => 'bg-brand-500'],
            ];
        @endphp

        <div class="space-y-6">
            @foreach($groups as $group)
                @if($group['items']->isNotEmpty())
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full {{ $group['dot'] }}"></span>
                            <h3 class="text-sm font-semibold {{ $group['accent'] }}">{{ $group['title'] }}</h3>
                            <span class="badge badge-neutral">{{ $group['items']->count() }}</span>
                        </div>

                        <div class="card divide-y divide-line/70">
                            @foreach($group['items'] as $f)
                                <div wire:key="cb-{{ $f->id }}"
                                     class="flex items-center gap-4 px-5 py-3.5 {{ $f->due_at->isPast() ? 'bg-rose-50/40' : '' }}">
                                    <span class="grid place-items-center w-10 h-10 rounded-full shrink-0
                                                 {{ $f->due_at->isPast() ? 'bg-rose-100 text-rose-600' : 'bg-brand-50 text-brand-600' }}">
                                        <x-call-desk.icon name="clock" class="w-5 h-5" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <div class="font-medium text-ink">{{ $f->inquiry->customer->name }}</div>
                                            @if($isManager)
                                                <span class="badge badge-neutral">{{ $f->user?->name ?? '—' }}</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-muted">
                                            {{ $f->inquiry->customer->phone }}
                                            @if($f->notes)<span class="text-slate-400"> · {{ $f->notes }}</span>@endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-semibold {{ $f->due_at->isPast() ? 'text-rose-600' : 'text-ink' }}">
                                            {{ $f->due_at->format('g:i A') }}
                                        </div>
                                        <div class="text-xs text-muted">{{ $f->due_at->format('d M') }} · {{ $f->due_at->diffForHumans() }}</div>
                                    </div>
                                    <button wire:click="markDone({{ $f->id }})" class="btn btn-ghost btn-sm shrink-0">
                                        <x-call-desk.icon name="check" class="w-4 h-4" /> Done
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
