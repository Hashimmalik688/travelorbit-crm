<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>New Call</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="{{ route('callcenter.dashboard') }}">Call Center</a> &rsaquo; New Call
            </div>
        </div>
    </div>

    @php
        $stepBadge = function ($n) {
            return '<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-semibold flex-shrink-0" style="width:28px;height:28px;font-size:.8rem;">'.$n.'</span>';
        };
    @endphp

    @if(! $activeCallId)
        {{-- Step 1 — customer + choose new vs. follow-up --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    {!! $stepBadge(1) !!}
                    <h6 class="mb-0">Find customer &amp; start the call</h6>
                </div>

                <label class="form-label">Search by phone number</label>
                <div class="input-group mb-1" style="max-width:420px;">
                    <input type="text" wire:model="phoneSearch" wire:keydown.enter.prevent="searchPhone" class="form-control" placeholder="e.g. 03001234567">
                    <button class="btn btn-outline-secondary" wire:click="searchPhone" type="button">
                        <i class="ph ph-magnifying-glass me-1"></i> Search
                    </button>
                </div>

                @if($matchedCustomerId)
                    <div class="alert alert-success py-2 px-3 mt-2 mb-0 small d-inline-flex align-items-center gap-2">
                        <i class="ph ph-check-circle"></i> Existing customer found — details filled in below.
                    </div>
                @elseif($custPhone)
                    <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-inline-flex align-items-center gap-2">
                        <i class="ph ph-plus"></i> No match — this will create a new customer.
                    </div>
                @endif

                {{-- Existing-inquiry picker --}}
                @if(! empty($customerInquiries) && $mode === null)
                    <div class="mt-3">
                        <div class="form-label mb-2">
                            This customer has {{ count($customerInquiries) }} previous {{ Str::plural('inquiry', count($customerInquiries)) }}. Log this call against:
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($customerInquiries as $inq)
                                <button type="button" wire:click="chooseExisting({{ $inq['id'] }})" class="btn btn-outline-secondary btn-sm">
                                    #{{ $inq['id'] }} · <span class="text-capitalize">{{ $inq['type'] }}</span>
                                    <span class="badge bg-label-secondary ms-1">{{ str_replace('_', ' ', $inq['status']) }}</span>
                                </button>
                            @endforeach
                            <button type="button" wire:click="chooseNew" class="btn btn-outline-primary btn-sm">
                                <i class="ph ph-plus me-1"></i> Start new inquiry
                            </button>
                        </div>
                    </div>
                @endif

                @if($mode === 'existing')
                    @php $chosen = collect($customerInquiries)->firstWhere('id', $selectedInquiryId); @endphp
                    <div class="mt-3 d-flex align-items-center justify-content-between rounded p-3 bg-label-primary">
                        <div class="small">
                            Logging a follow-up call for inquiry <strong>#{{ $selectedInquiryId }}</strong>
                            @if($chosen)({{ ucfirst($chosen['type']) }})@endif
                        </div>
                        <button type="button" wire:click="backToPicker" class="btn btn-sm btn-link">Change</button>
                    </div>
                @endif

                @if($mode === 'new')
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Customer name</label>
                            <input type="text" wire:model="custName" class="form-control" placeholder="Full name">
                            @error('custName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" wire:model="custPhone" class="form-control" placeholder="Phone number">
                            @error('custPhone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" wire:model="custCity" class="form-control" placeholder="City">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Step 2 — inquiry (only for a brand-new inquiry) --}}
        @if($mode === 'new')
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        {!! $stepBadge(2) !!}
                        <h6 class="mb-0">What do they want?</h6>
                    </div>

                    <div class="mb-3" style="max-width:320px;">
                        <label class="form-label">How did they reach us?</label>
                        <select wire:model="source" class="form-select">
                            <option value="">Select a source…</option>
                            @foreach($this->sources() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('source') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <label class="form-label">Inquiry type</label>
                    <div class="d-flex flex-wrap gap-2 mb-1">
                        @foreach(['flight' => 'Flight', 'hotel' => 'Hotel', 'holiday' => 'Holiday', 'umrah' => 'Umrah', 'visa' => 'Visa'] as $key => $label)
                            <button type="button" wire:click="$set('type', '{{ $key }}')"
                                class="btn btn-sm {{ $type === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    @error('type') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                    <label class="form-label mt-3">Passengers</label>
                    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                        <div style="width:90px;">
                            <span class="text-muted small">Adults</span>
                            <input type="number" min="1" wire:model="adults" class="form-control text-center">
                        </div>
                        <div style="width:90px;">
                            <span class="text-muted small">Children</span>
                            <input type="number" min="0" wire:model="children" class="form-control text-center">
                        </div>
                        <div style="width:90px;">
                            <span class="text-muted small">Infants</span>
                            <input type="number" min="0" wire:model="infants" class="form-control text-center">
                        </div>
                    </div>

                    @if($type === 'flight')
                        <div wire:key="details-flight" class="rounded bg-light border p-3">
                            <div class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Flight details</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Dep. airport</label>
                                    <input type="text" wire:model="details.dep_airport" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Arrival airport</label>
                                    <input type="text" wire:model="details.arrival_airport" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Flight type</label>
                                    <select wire:model.live="details.flight_type" class="form-select">
                                        <option value="">Select…</option>
                                        <option value="one_way">One way</option>
                                        <option value="return">Return</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dep. date</label>
                                    <input type="date" wire:model="details.dep_date" class="form-control">
                                </div>
                                @if(($details['flight_type'] ?? '') === 'return')
                                    <div class="col-md-4">
                                        <label class="form-label">Return date</label>
                                        <input type="date" wire:model="details.return_date" class="form-control">
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label">Cabin class</label>
                                    <select wire:model="details.cabin_class" class="form-select">
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
                        <div wire:key="details-hotel" class="rounded bg-light border p-3">
                            <div class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Hotel details</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Destination</label>
                                    <input type="text" wire:model="details.destination" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Check-in</label>
                                    <input type="date" wire:model="details.check_in" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Check-out</label>
                                    <input type="date" wire:model="details.check_out" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rooms</label>
                                    <input type="number" min="1" wire:model="details.rooms" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Star rating</label>
                                    <input type="text" wire:model="details.star_rating" class="form-control">
                                </div>
                            </div>
                        </div>
                    @elseif($type === 'holiday')
                        <div wire:key="details-holiday" class="rounded bg-light border p-3">
                            <div class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Holiday details</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Package name</label>
                                    <input type="text" wire:model="details.package_name" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Destination</label>
                                    <input type="text" wire:model="details.destination" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Travel month</label>
                                    <input type="text" wire:model="details.travel_month" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Budget limit</label>
                                    <input type="text" wire:model="details.budget_limit" class="form-control" placeholder="e.g. PKR 200,000">
                                </div>
                            </div>
                        </div>
                    @elseif($type === 'umrah')
                        <div wire:key="details-umrah" class="rounded bg-light border p-3">
                            <div class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Umrah details</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Package name</label>
                                    <input type="text" wire:model="details.package_name" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Travel date</label>
                                    <input type="date" wire:model="details.travel_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Package type</label>
                                    <input type="text" wire:model="details.package_type" class="form-control" placeholder="economy / vip">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Makkah nights</label>
                                    <input type="number" min="0" wire:model="details.makkah_nights" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Madinah nights</label>
                                    <input type="number" min="0" wire:model="details.madinah_nights" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Transport</label>
                                    <select wire:model="details.transport" class="form-select">
                                        <option value="">Select…</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Need flight</label>
                                    <select wire:model="details.need_flight" class="form-select">
                                        <option value="">Select…</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @elseif($type === 'visa')
                        <div wire:key="details-visa" class="rounded bg-light border p-3">
                            <div class="text-uppercase text-muted mb-2" style="font-size:.68rem;letter-spacing:.06em;">Visa details</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <input type="text" wire:model="details.country" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Visa type</label>
                                    <input type="text" wire:model="details.visa_type" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Travel date</label>
                                    <input type="date" wire:model="details.travel_date" class="form-control">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($mode)
            <div class="d-flex justify-content-end">
                <button type="button" wire:click="addCall" wire:loading.attr="disabled" class="btn btn-primary">
                    <span wire:loading.remove wire:target="addCall">Add Call</span>
                    <span wire:loading wire:target="addCall">Starting…</span>
                </button>
            </div>
        @endif
    @else
        {{-- Step 2 — log the outcome, only after a call has been started --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        {!! $stepBadge(2) !!}
                        <h6 class="mb-0">Log the outcome</h6>
                    </div>
                    <span class="badge bg-label-secondary">Call #{{ $activeCallId }} · {{ $activeInquiryLabel }}</span>
                </div>

                <div class="mb-3" style="max-width:320px;">
                    <label class="form-label">Disposition</label>
                    <select wire:model.live="disposition" class="form-select">
                        <option value="">Select disposition…</option>
                        @foreach($this->dispositions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('disposition') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Caller Comment</label>
                        <textarea wire:model="callerComment" class="form-control" rows="3" placeholder="What the caller said…"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agent Comments</label>
                        <textarea wire:model="agentComment" class="form-control" rows="3" placeholder="Your notes / next steps…"></textarea>
                    </div>
                </div>

                @if(\App\Models\CallCenterCall::isBookedDisposition($disposition))
                    <div class="mb-3" style="max-width:320px;">
                        <label class="form-label">MIS #</label>
                        <input type="text" wire:model="misNumber" class="form-control" placeholder="Booking software reference">
                        @error('misNumber') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="form-check">
                        <input type="checkbox" wire:model.live="scheduleCallback" class="form-check-input" id="scheduleCallback">
                        <label class="form-check-label" for="scheduleCallback">
                            Schedule a callback
                            <div class="text-muted small">Set a reminder to call this customer back</div>
                        </label>
                    </div>
                    @if($scheduleCallback)
                        <div class="mt-3" style="max-width:280px;">
                            <label class="form-label">Callback date &amp; time</label>
                            <input type="datetime-local" wire:model="callbackAt" class="form-control">
                            @error('callbackAt') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" wire:click="saveCall" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="saveCall">Save call</span>
                        <span wire:loading wire:target="saveCall">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
