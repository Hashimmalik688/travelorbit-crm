<div class="max-w-3xl">
    @if(! $activeCallId)
        {{-- Step 1 — customer + choose new vs. follow-up --}}
        <section class="card p-5 mb-4">
            <div class="flex items-center gap-3 mb-4">
                <span class="grid place-items-center w-7 h-7 rounded-full bg-brand-600 text-white text-sm font-semibold shrink-0">1</span>
                <h3 class="font-semibold text-ink">Find customer &amp; start the call</h3>
            </div>

            <label class="field-label">Search by phone number</label>
            <div class="flex gap-2 max-w-md">
                <input type="text" wire:model="phoneSearch" wire:keydown.enter.prevent="searchPhone"
                       class="input" placeholder="e.g. 03001234567">
                <button class="btn btn-ghost shrink-0" wire:click="searchPhone">
                    <x-call-desk.icon name="search" class="w-4 h-4" /> Search
                </button>
            </div>

            @if($matchedCustomerId)
                <div class="mt-3 flex items-center gap-2 rounded-lg bg-emerald-50 text-emerald-700 px-3 py-2 text-sm ring-1 ring-inset ring-emerald-200">
                    <x-call-desk.icon name="check-circle" class="w-4 h-4" /> Existing customer found — details filled in below.
                </div>
            @elseif($custPhone)
                <div class="mt-3 flex items-center gap-2 rounded-lg bg-amber-50 text-amber-700 px-3 py-2 text-sm ring-1 ring-inset ring-amber-200">
                    <x-call-desk.icon name="plus" class="w-4 h-4" /> No match — this will create a new customer.
                </div>
            @endif

            {{-- Existing-inquiry picker --}}
            @if(! empty($customerInquiries) && $mode === null)
                <div class="mt-4">
                    <div class="field-label mb-2">
                        This customer has {{ count($customerInquiries) }} previous {{ Str::plural('inquiry', count($customerInquiries)) }}. Log this call against:
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($customerInquiries as $inq)
                            <button type="button" wire:click="chooseExisting({{ $inq['id'] }})"
                                    class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                #{{ $inq['id'] }} · <span class="capitalize">{{ $inq['type'] }}</span>
                                <span class="badge badge-{{ $inq['status'] }}">{{ str_replace('_', ' ', $inq['status']) }}</span>
                            </button>
                        @endforeach
                        <button type="button" wire:click="chooseNew"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-100 transition cursor-pointer">
                            <x-call-desk.icon name="plus" class="w-4 h-4" /> Start new inquiry
                        </button>
                    </div>
                </div>
            @endif

            @if($mode === 'existing')
                @php $chosen = collect($customerInquiries)->firstWhere('id', $selectedInquiryId); @endphp
                <div class="mt-4 flex items-center justify-between rounded-lg bg-brand-50 border border-brand-100 px-4 py-3">
                    <div class="text-sm text-brand-800">
                        Logging a follow-up call for inquiry <strong>#{{ $selectedInquiryId }}</strong>
                        @if($chosen)({{ ucfirst($chosen['type']) }})@endif
                    </div>
                    <button type="button" wire:click="backToPicker" class="text-xs font-medium text-brand-700 hover:underline">Change</button>
                </div>
            @endif

            @if($mode === 'new')
                <div class="grid gap-3 sm:grid-cols-3 mt-4">
                    <div>
                        <label class="field-label">Customer name</label>
                        <input type="text" wire:model="custName" class="input" placeholder="Full name">
                        @error('custName') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" wire:model="custPhone" class="input" placeholder="Phone number">
                        @error('custPhone') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="field-label">City</label>
                        <input type="text" wire:model="custCity" class="input" placeholder="City">
                    </div>
                </div>
            @endif
        </section>

        {{-- Step 2 — inquiry details (only for a brand-new inquiry) --}}
        @if($mode === 'new')
            <section class="card p-5 mb-4">
                <div class="flex items-center gap-3 mb-4">
                    <span class="grid place-items-center w-7 h-7 rounded-full bg-brand-600 text-white text-sm font-semibold shrink-0">2</span>
                    <h3 class="font-semibold text-ink">What do they want?</h3>
                </div>

                <div class="max-w-xs mb-4">
                    <label class="field-label">How did they reach us?</label>
                    <select wire:model="source" class="select">
                        <option value="">Select a source…</option>
                        @foreach($this->sources() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('source') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <label class="field-label">Inquiry type</label>
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach(['flight' => 'Flight', 'hotel' => 'Hotel', 'holiday' => 'Holiday', 'umrah' => 'Umrah', 'visa' => 'Visa'] as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" class="peer sr-only" wire:model.live="type" value="{{ $key }}">
                            <span class="inline-flex items-center rounded-lg border border-line bg-white px-4 py-2 text-sm font-medium text-slate-600
                                         transition hover:bg-slate-50
                                         peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('type') <div class="text-rose-600 text-xs mb-3">{{ $message }}</div> @enderror

                <label class="field-label">Passengers</label>
                <div class="flex flex-wrap items-end gap-3 mb-4">
                    <div class="w-20">
                        <span class="text-xs text-muted">Adults</span>
                        <input type="number" min="1" wire:model="adults" class="input text-center">
                    </div>
                    <div class="w-20">
                        <span class="text-xs text-muted">Children</span>
                        <input type="number" min="0" wire:model="children" class="input text-center">
                    </div>
                    <div class="w-20">
                        <span class="text-xs text-muted">Infants</span>
                        <input type="number" min="0" wire:model="infants" class="input text-center">
                    </div>
                </div>

                @if($type === 'flight')
                    <div wire:key="details-flight" class="rounded-lg bg-slate-50 border border-line p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Flight details</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="field-label">Dep. airport</label>
                                <input type="text" wire:model="details.dep_airport" class="input">
                            </div>
                            <div>
                                <label class="field-label">Arrival airport</label>
                                <input type="text" wire:model="details.arrival_airport" class="input">
                            </div>
                            <div>
                                <label class="field-label">Flight type</label>
                                <select wire:model.live="details.flight_type" class="select">
                                    <option value="">Select…</option>
                                    <option value="one_way">One way</option>
                                    <option value="return">Return</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Dep. date</label>
                                <input type="date" wire:model="details.dep_date" class="input">
                            </div>
                            @if(($details['flight_type'] ?? '') === 'return')
                                <div>
                                    <label class="field-label">Return date</label>
                                    <input type="date" wire:model="details.return_date" class="input">
                                </div>
                            @endif
                            <div>
                                <label class="field-label">Cabin class</label>
                                <select wire:model="details.cabin_class" class="select">
                                    <option value="">Select…</option>
                                    <option value="economy">Economy</option>
                                    <option value="premium_economy">Premium Economy</option>
                                    <option value="business">Business</option>
                                    <option value="first">First Class</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @elseif($type === 'hotel')
                    <div wire:key="details-hotel" class="rounded-lg bg-slate-50 border border-line p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Hotel details</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="field-label">Destination</label>
                                <input type="text" wire:model="details.destination" class="input">
                            </div>
                            <div>
                                <label class="field-label">Check-in</label>
                                <input type="date" wire:model="details.check_in" class="input">
                            </div>
                            <div>
                                <label class="field-label">Check-out</label>
                                <input type="date" wire:model="details.check_out" class="input">
                            </div>
                            <div>
                                <label class="field-label">Rooms</label>
                                <input type="number" min="1" wire:model="details.rooms" class="input">
                            </div>
                            <div>
                                <label class="field-label">Star rating</label>
                                <input type="text" wire:model="details.star_rating" class="input">
                            </div>
                        </div>
                    </div>
                @elseif($type === 'holiday')
                    <div wire:key="details-holiday" class="rounded-lg bg-slate-50 border border-line p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Holiday details</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="field-label">Package name</label>
                                <input type="text" wire:model="details.package_name" class="input">
                            </div>
                            <div>
                                <label class="field-label">Destination</label>
                                <input type="text" wire:model="details.destination" class="input">
                            </div>
                            <div>
                                <label class="field-label">Travel month</label>
                                <input type="text" wire:model="details.travel_month" class="input">
                            </div>
                            <div>
                                <label class="field-label">Budget limit</label>
                                <input type="text" wire:model="details.budget_limit" class="input" placeholder="e.g. PKR 200,000">
                            </div>
                        </div>
                    </div>
                @elseif($type === 'umrah')
                    <div wire:key="details-umrah" class="rounded-lg bg-slate-50 border border-line p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Umrah details</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="field-label">Package name</label>
                                <input type="text" wire:model="details.package_name" class="input">
                            </div>
                            <div>
                                <label class="field-label">Travel date</label>
                                <input type="date" wire:model="details.travel_date" class="input">
                            </div>
                            <div>
                                <label class="field-label">Package type</label>
                                <input type="text" wire:model="details.package_type" class="input" placeholder="economy / vip">
                            </div>
                            <div>
                                <label class="field-label">Makkah nights</label>
                                <input type="number" min="0" wire:model="details.makkah_nights" class="input">
                            </div>
                            <div>
                                <label class="field-label">Madinah nights</label>
                                <input type="number" min="0" wire:model="details.madinah_nights" class="input">
                            </div>
                            <div>
                                <label class="field-label">Transport</label>
                                <select wire:model="details.transport" class="select">
                                    <option value="">Select…</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Need flight</label>
                                <select wire:model="details.need_flight" class="select">
                                    <option value="">Select…</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @elseif($type === 'visa')
                    <div wire:key="details-visa" class="rounded-lg bg-slate-50 border border-line p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Visa details</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="field-label">Country</label>
                                <input type="text" wire:model="details.country" class="input">
                            </div>
                            <div>
                                <label class="field-label">Visa type</label>
                                <input type="text" wire:model="details.visa_type" class="input">
                            </div>
                            <div>
                                <label class="field-label">Travel date</label>
                                <input type="date" wire:model="details.travel_date" class="input">
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        @if($mode)
            <div class="flex justify-end">
                <button wire:click="addCall" wire:loading.attr="disabled" class="btn btn-primary">
                    <span wire:loading.remove wire:target="addCall">Add Call</span>
                    <span wire:loading wire:target="addCall">Starting…</span>
                </button>
            </div>
        @endif
    @else
        {{-- Step 2 — log the outcome, only after a call has been started --}}
        <section class="card p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center w-7 h-7 rounded-full bg-brand-600 text-white text-sm font-semibold shrink-0">2</span>
                    <h3 class="font-semibold text-ink">Log the outcome</h3>
                </div>
                <span class="badge badge-neutral">Call #{{ $activeCallId }} · {{ $activeInquiryLabel }}</span>
            </div>

            <div class="max-w-sm mb-4">
                <label class="field-label">Disposition</label>
                <select wire:model.live="disposition" class="select">
                    <option value="">Select disposition…</option>
                    @foreach($this->dispositions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('disposition') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2 mb-4">
                <div>
                    <label class="field-label">Caller Comment</label>
                    <textarea wire:model="callerComment" class="textarea" rows="3" placeholder="What the caller said…"></textarea>
                </div>
                <div>
                    <label class="field-label">Agent Comments</label>
                    <textarea wire:model="agentComment" class="textarea" rows="3" placeholder="Your notes / next steps…"></textarea>
                </div>
            </div>

            @if(\App\Models\CallCenterCall::isBookedDisposition($disposition))
                <div class="max-w-xs mb-4">
                    <label class="field-label">MIS #</label>
                    <input type="text" wire:model="misNumber" class="input" placeholder="Booking software reference">
                    @error('misNumber') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
            @endif

            {{-- Schedule Callback --}}
            <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model.live="scheduleCallback" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <div>
                        <span class="text-sm font-medium text-ink">Schedule a callback</span>
                        <p class="text-xs text-muted">Set a reminder to call this customer back</p>
                    </div>
                </label>
                @if($scheduleCallback)
                    <div class="mt-3 max-w-xs">
                        <label class="field-label">Callback date &amp; time</label>
                        <input type="datetime-local" wire:model="callbackAt" class="input">
                        @error('callbackAt') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-1">
                <button wire:click="saveCall" wire:loading.attr="disabled" class="btn btn-primary">
                    <span wire:loading.remove wire:target="saveCall">Save call</span>
                    <span wire:loading wire:target="saveCall">Saving…</span>
                </button>
            </div>
        </section>
    @endif
</div>
