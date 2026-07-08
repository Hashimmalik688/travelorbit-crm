<div>
    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-call-desk.icon name="search" class="w-4 h-4" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" class="input pl-9"
                       placeholder="Search customer name or phone…">
            </div>
            <div class="flex flex-wrap gap-2 sm:ml-auto">
                <select wire:model.live="filterType" class="select !w-auto">
                    <option value="">All types</option>
                    <option value="flight">Flight</option>
                    <option value="hotel">Hotel</option>
                    <option value="holiday">Holiday</option>
                    <option value="umrah">Umrah</option>
                    <option value="visa">Visa</option>
                </select>
                <select wire:model.live="filterDisposition" class="select !w-auto">
                    <option value="">All dispositions</option>
                    @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterMonth" class="select !w-auto">
                    <option value="">All months</option>
                    @for($m = 0; $m < 12; $m++)
                        @php $d = now()->startOfMonth()->subMonths($m); @endphp
                        <option value="{{ $d->format('Y-m') }}">{{ $d->format('M Y') }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted whitespace-nowrap">Date range:</span>
            <input type="date" wire:model.live="filterDateFrom" class="input !w-auto text-sm" title="From date">
            <span class="text-xs text-muted">to</span>
            <input type="date" wire:model.live="filterDateTo" class="input !w-auto text-sm" title="To date">
            @if($filterDateFrom || $filterDateTo || $filterMonth)
                <button wire:click="clearDates" class="text-xs text-brand-600 hover:text-brand-800 font-medium whitespace-nowrap">Clear dates</button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        @if($isManager)<th>Agent</th>@endif
                        <th>Pax</th>
                        <th>Disposition</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inq)
                        <tr wire:key="inq-{{ $inq->id }}" wire:click="open({{ $inq->id }})"
                            class="cursor-pointer hover:bg-slate-50 transition-colors">
                            <td>
                                <div class="font-medium text-ink">{{ $inq->customer->name }}</div>
                                <div class="text-xs text-muted">{{ $inq->customer->phone }}</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 text-slate-600 capitalize">
                                    <x-call-desk.icon name="{{ ['flight'=>'plane','hotel'=>'building','holiday'=>'map-pin','umrah'=>'calendar','visa'=>'inbox'][$inq->type] ?? 'inbox' }}" class="w-4 h-4 text-slate-400" />
                                    {{ $inq->type }}
                                </span>
                            </td>
                            @if($isManager)<td>{{ $inq->user?->name ?? '—' }}</td>@endif
                            <td>{{ $inq->totalPax() }}</td>
                            <td>
                                @if($inq->last_disposition)
                                    @php
                                        $dispLabel = \App\Models\CallCenterCall::dispositions()[$inq->last_disposition] ?? $inq->last_disposition;
                                        $dispColor = match($inq->last_disposition) {
                                            'booked', 'booked_by_us', 'already_booked' => 'badge-converted',
                                            'wrong_number', 'booked_elsewhere' => 'badge-lost',
                                            'no_answer' => 'badge-new',
                                            default => 'badge-in_progress',
                                        };
                                    @endphp
                                    <span class="badge {{ $dispColor }}">{{ $dispLabel }}</span>
                                @else
                                    <span class="badge badge-new">New</span>
                                @endif
                            </td>
                            <td class="text-muted whitespace-nowrap">{{ $inq->created_at->format('d M, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isManager ? 6 : 5 }}" class="text-center py-12">
                                <div class="mx-auto w-12 h-12 grid place-items-center rounded-full bg-slate-50 text-slate-300 mb-3">
                                    <x-call-desk.icon name="inbox" class="w-6 h-6" />
                                </div>
                                <p class="text-sm text-muted">No inquiries match your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $inquiries->links() }}</div>

    {{-- Detail drawer --}}
    @if($selected)
        <div class="fixed inset-0 z-40" wire:key="drawer-{{ $selected->id }}">
            <div class="absolute inset-0 bg-slate-900/40" wire:click="close"></div>

            <aside class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl flex flex-col">
                <div class="h-16 flex items-center justify-between px-5 border-b border-line shrink-0">
                    <div class="flex items-center gap-2">
                        @if($selected->last_disposition)
                            @php
                                $dColor = match($selected->last_disposition) {
                                    'booked', 'booked_by_us', 'already_booked' => 'badge-converted',
                                    'wrong_number', 'booked_elsewhere' => 'badge-lost',
                                    'no_answer' => 'badge-new',
                                    default => 'badge-in_progress',
                                };
                            @endphp
                            <span class="badge {{ $dColor }}">{{ \App\Models\CallCenterCall::dispositions()[$selected->last_disposition] ?? $selected->last_disposition }}</span>
                        @else
                            <span class="badge badge-new">New</span>
                        @endif
                        <h3 class="font-semibold text-ink">Inquiry #{{ $selected->id }}</h3>
                    </div>
                    <button wire:click="close" class="grid place-items-center w-8 h-8 rounded-lg text-slate-400 hover:text-ink hover:bg-slate-100">
                        <x-call-desk.icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-6">
                    {{-- Customer --}}
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid place-items-center w-11 h-11 rounded-full bg-brand-100 text-brand-700 font-semibold">
                                {{ strtoupper(substr($selected->customer->name, 0, 1)) }}
                            </span>
                            <div>
                                <div class="font-semibold text-ink">{{ $selected->customer->name }}</div>
                                <div class="text-sm text-muted">{{ $selected->customer->phone }}{{ $selected->customer->city ? ' · '.$selected->customer->city : '' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-50 border border-line px-3 py-2">
                            <div class="text-xs text-muted">Type</div>
                            <div class="font-medium text-ink capitalize">{{ $selected->type }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 border border-line px-3 py-2">
                            <div class="text-xs text-muted">Passengers</div>
                            <div class="font-medium text-ink">{{ $selected->totalPax() }} ({{ $selected->adults }}A · {{ $selected->children }}C · {{ $selected->infants }}I)</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 border border-line px-3 py-2">
                            <div class="text-xs text-muted">Source</div>
                            <div class="font-medium text-ink">{{ \App\Models\CallCenterInquiry::sources()[$selected->source] ?? $selected->source }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 border border-line px-3 py-2">
                            <div class="text-xs text-muted">Agent</div>
                            <div class="font-medium text-ink">{{ $selected->user?->name ?? '—' }}</div>
                        </div>
                        @if($selected->mis_number)
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 col-span-2">
                                <div class="text-xs text-emerald-600">MIS #</div>
                                <div class="font-medium text-emerald-800">{{ $selected->mis_number }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Trip details --}}
                    @php $fields = \App\Models\CallCenterInquiry::detailFieldsFor($selected->type); @endphp
                    @if(count($fields))
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Trip details</h4>
                            <dl class="divide-y divide-line/70 rounded-lg border border-line overflow-hidden">
                                @foreach($fields as [$key, $label, $t])
                                    <div class="flex justify-between gap-4 px-3 py-2 text-sm">
                                        <dt class="text-muted">{{ $label }}</dt>
                                        <dd class="font-medium text-ink text-right">{{ \App\Models\CallCenterInquiry::detailValueLabel($key, $selected->detail($key)) }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    {{-- Call history --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Call history</h4>
                        @if($selected->calls->isEmpty())
                            <p class="text-sm text-muted">No calls logged.</p>
                        @else
                            <ol class="relative border-l border-line ml-1.5 space-y-4">
                                @foreach($selected->calls as $call)
                                    <li class="ml-4">
                                        <span class="absolute -left-[7px] mt-1 w-3 h-3 rounded-full {{ $call->disposition ? 'bg-brand-500' : 'bg-amber-400' }} ring-4 ring-white"></span>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-medium text-ink">
                                                {{ $call->disposition ? (\App\Models\CallCenterCall::dispositions()[$call->disposition] ?? $call->disposition) : 'In progress' }}
                                            </span>
                                            @if(! $call->disposition)
                                                <span class="badge badge-quoted">open</span>
                                            @endif
                                        </div>
                                        @if($call->caller_comment)
                                            <p class="text-sm text-slate-600 mt-1"><span class="font-medium text-slate-500">Caller:</span> {{ $call->caller_comment }}</p>
                                        @endif
                                        @if($call->agent_comment)
                                            <p class="text-sm text-slate-600 mt-0.5"><span class="font-medium text-slate-500">Agent:</span> {{ $call->agent_comment }}</p>
                                        @endif
                                        <div class="text-xs text-muted mt-1">
                                            {{ $call->called_at?->format('d M Y, g:i A') }}{{ $call->user ? ' · '.$call->user->name : '' }}
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>

                    {{-- Scheduled callbacks --}}
                    @if($selected->followups->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Scheduled callbacks</h4>
                            <ul class="space-y-2">
                                @foreach($selected->followups as $f)
                                    <li class="flex items-center gap-2 text-sm rounded-lg border border-line px-3 py-2">
                                        <x-call-desk.icon name="{{ $f->status === 'done' ? 'check-circle' : 'clock' }}" class="w-4 h-4 {{ $f->status === 'done' ? 'text-emerald-500' : 'text-amber-500' }}" />
                                        <span class="text-ink">{{ $f->due_at->format('d M, g:i A') }}</span>
                                        <span class="badge {{ $f->status === 'done' ? 'badge-converted' : 'badge-quoted' }} ml-auto">{{ $f->status }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Follow-up action --}}
                    @if($followUpCallId && $followUpInquiryId === $selected->id)
                        <div class="rounded-lg border-2 border-brand-200 bg-brand-50/50 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-brand-800">Log follow-up call</h4>
                                <button wire:click="cancelFollowUp" class="text-xs text-slate-500 hover:text-slate-700">Cancel</button>
                            </div>

                            <div>
                                <label class="field-label">Disposition</label>
                                <select wire:model.live="fuDisposition" class="select">
                                    <option value="">Select disposition…</option>
                                    @foreach(\App\Models\CallCenterCall::dispositions() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('fuDisposition') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="field-label">Caller Comment</label>
                                <textarea wire:model="fuCallerComment" class="textarea" rows="2" placeholder="What the caller said…"></textarea>
                            </div>
                            <div>
                                <label class="field-label">Agent Comments</label>
                                <textarea wire:model="fuAgentComment" class="textarea" rows="2" placeholder="Your notes / next steps…"></textarea>
                            </div>

                            @if(\App\Models\CallCenterCall::isBookedDisposition($fuDisposition))
                                <div>
                                    <label class="field-label">MIS #</label>
                                    <input type="text" wire:model="fuMisNumber" class="input" placeholder="Booking software reference">
                                    @error('fuMisNumber') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            {{-- Schedule Callback --}}
                            <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-3">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="fuScheduleCallback" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <span class="text-sm font-medium text-ink">Schedule a callback</span>
                                        <p class="text-xs text-muted">Set a reminder to call back</p>
                                    </div>
                                </label>
                                @if($fuScheduleCallback)
                                    <div class="mt-2">
                                        <label class="field-label">Callback date &amp; time</label>
                                        <input type="datetime-local" wire:model="fuCallbackAt" class="input">
                                        @error('fuCallbackAt') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                                    </div>
                                @endif
                            </div>

                            <div class="flex justify-end pt-1">
                                <button wire:click="saveFollowUp" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                    <span wire:loading.remove wire:target="saveFollowUp">Save call</span>
                                    <span wire:loading wire:target="saveFollowUp">Saving…</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="pt-2">
                            <button wire:click="startFollowUp({{ $selected->id }})" class="btn btn-primary w-full">
                                <x-call-desk.icon name="phone" class="w-4 h-4" /> Follow Up
                            </button>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    @endif
</div>
