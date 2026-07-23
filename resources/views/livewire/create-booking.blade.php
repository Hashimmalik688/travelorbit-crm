<div class="col-12">
  <div class="card border-0" style="border-radius: 24px; background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.88) 100%); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); box-shadow: 0 8px 40px rgba(51,46,158,0.12), 0 2px 8px rgba(51,46,158,0.06); border: 1px solid rgba(255,255,255,0.6);">
    <div class="card-body p-0">

      @if (session()->has('success'))
        <div class="alert alert-success border-0 rounded-0 m-0 py-3 px-4 fw-semibold">{{ session('success') }}</div>
      @endif
      @if (session()->has('error'))
        <div class="alert alert-danger border-0 rounded-0 m-0 py-3 px-4 fw-semibold">{{ session('error') }}</div>
      @endif

      @if ($booking_ref && !$errors->any())
        <div class="text-center py-5">
          <div class="mb-3" style="font-size:3rem;">✓</div>
          <h4 class="fw-bold mb-2">Booking #{{ $booking_ref }}</h4>
          <p class="text-muted mb-4">Saved successfully.</p>
          <a href="{{ route('bookings.index') }}" class="btn btn-primary px-4">View All Bookings</a>
        </div>
      @else
        {{-- Step Indicator - Glassmorphism --}}
        <div class="px-4 pt-4 pb-0" style="background: linear-gradient(180deg, rgba(79,70,229,0.04) 0%, transparent 100%);">
          <div class="d-flex align-items-center gap-0">
            @foreach ($this->stepLabels as $i => $s)
              @php $num = $i + 1; @endphp
              <div class="d-flex align-items-center {{ $loop->last ? '' : 'flex-grow-1' }}">
                <div class="d-flex align-items-center gap-2">
                  <div class="d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                    style="width:34px; height:34px; border-radius:12px; font-size:0.96rem;
                    background: {{ $step === $num ? 'linear-gradient(135deg, #4F46E5 0%, #6366F1 100%)' : ($step > $num ? 'linear-gradient(135deg, #10B981 0%, #34D399 100%)' : 'linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%)') }};
                    box-shadow: {{ $step === $num ? '0 4px 16px rgba(79,70,229,0.4)' : 'none' }};
                    border: {{ $step === $num ? 'none' : '1px solid rgba(255,255,255,0.3)' }}">
                    @if ($step > $num) ✓ @else {{ $num }} @endif
                  </div>
                  <span class="small fw-semibold text-nowrap {{ $step === $num ? 'text-primary' : ($step > $num ? 'text-success' : 'text-muted') }}" style="font-size:0.96rem;">{{ $s['label'] }}</span>
                </div>
                @if (!$loop->last)
                  <div class="flex-grow-1 mx-2" style="height:3px; min-width:12px; border-radius:3px;
                    background: {{ $step > $num ? 'linear-gradient(90deg, #10B981, #34D399)' : 'rgba(51,46,158,0.1)' }};"></div>
                @endif
              </div>
            @endforeach
          </div>
        </div>

        <hr class="my-3 mx-4" style="border-color: rgba(51,46,158,0.06)">

        <form wire:submit.prevent="save" onkeydown="return event.key !== 'Enter';">
          @csrf


          <div class="px-4 pb-4" id="step-content">

            {{-- ===== LEAD & CALLER: Booking Info + Caller Details ===== --}}
            @if ($this->currentStepId === 'lead-caller')
              <div wire:key="step-lead-caller">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <h6 class="fw-bold mb-0" style="color: var(--to-charcoal); font-size:1.14rem;">
                    <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                    Lead &amp; Caller Information
                  </h6>
                  <div class="text-end" style="line-height:1;">
                    <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Booking # This Month</div>
                    <div class="fw-bold" style="font-size:2.2rem; color:var(--to-charcoal); line-height:1;">{{ $this->bookingCountThisMonth }}</div>
                  </div>
                </div>

                {{-- Lead Info --}}
                <div class="row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Lead Source <span class="text-danger">*</span></label>
                    <x-styled-select modelName="lead_source" :placeholder="'Select source'" :optgroup="true" :options="[
                      ['label' => 'TravelOrbit', 'options' => [
                        ['value' => 'to_returning', 'label' => 'TO Returning'],
                        ['value' => 'to_referral', 'label' => 'TO Referral'],
                      ]],
                      ['label' => 'Client Type', 'options' => [
                        ['value' => 'referral_client', 'label' => 'Referral Client'],
                        ['value' => 'returning_client', 'label' => 'Returning Client'],
                        ['value' => 'personal', 'label' => 'Personal'],
                      ]],
                      ['label' => 'Social & Channels', 'options' => [
                        ['value' => 'fb', 'label' => 'Facebook'],
                        ['value' => 'wa', 'label' => 'WhatsApp'],
                        ['value' => 'email', 'label' => 'Email'],
                        ['value' => 'diaspora_group', 'label' => 'Diaspora Group'],
                        ['value' => 'instagram', 'label' => 'Instagram'],
                        ['value' => 'tiktok', 'label' => 'TikTok'],
                        ['value' => 'website', 'label' => 'Website'],
                        ['value' => 'google', 'label' => 'Google'],
                      ]],
                    ]" />
                    @error('lead_source') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Lead Nature <span class="text-danger">*</span></label>
                    <x-styled-select modelName="lead_nature" :placeholder="'Select nature'" :optgroup="false" :options="[
                      ['value' => 'new_booking', 'label' => 'New Booking'],
                      ['value' => 'date_change', 'label' => 'Date Change'],
                      ['value' => 'refund_booking', 'label' => 'Refund Booking'],
                      ['value' => 'previous_booking', 'label' => 'Previous Booking'],
                    ]" />
                    @error('lead_nature') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Booking Type <span class="text-danger">*</span></label>
                    <x-styled-select modelName="booking_type" :placeholder="'Select type'" :optgroup="false" :live="true" :options="[
                      ['value' => 'flight', 'label' => 'Flight'],
                      ['value' => 'hotel', 'label' => 'Hotel'],
                      ['value' => 'holiday', 'label' => 'Holidays'],
                      ['value' => 'umrah', 'label' => 'Umrah'],
                      ['value' => 'visa', 'label' => 'Visa'],
                      ['value' => 'transfers', 'label' => 'Transfers'],
                      ['value' => 'excursion', 'label' => 'Excursion'],
                    ]" />
                    @error('booking_type') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Last Payment <span class="text-danger">*</span></label>
                    <x-date-picker modelName="last_payment_date" placeholder="Last Payment" />
                    @error('last_payment_date') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Last Issue <span class="text-danger">*</span></label>
                    <x-date-picker modelName="last_issue_date" placeholder="Last Issue" />
                    @error('last_issue_date') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                </div>

                <hr class="my-3" style="border-color: rgba(51,46,158,0.08);">

                {{-- Caller Info --}}
                <h6 class="fw-semibold mb-2" style="font-size:0.936rem; color:#5A6080;">Caller Details</h6>
                <div class="row g-3 mb-3">
                  <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Title</label>
                    <x-styled-select modelName="booker_title" :placeholder="'-'" :optgroup="false" :options="[
                      ['value' => '1', 'label' => 'Mr.'],
                      ['value' => '2', 'label' => 'Ms.'],
                      ['value' => '3', 'label' => 'Mrs.'],
                      ['value' => '4', 'label' => 'Mstr'],
                      ['value' => '5', 'label' => 'Miss'],
                      ['value' => '6', 'label' => 'Dr.'],
                    ]" />
                    @error('booker_title') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">First Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model.lazy="booker_first_name" class="form-control @error('booker_first_name') is-invalid @enderror" style="border-radius:10px;" placeholder="John">
                    @error('booker_first_name') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Last Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model.lazy="booker_last_name" class="form-control @error('booker_last_name') is-invalid @enderror" style="border-radius:10px;" placeholder="Doe">
                    @error('booker_last_name') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Mobile <span class="text-danger">*</span></label>
                    <input type="tel" inputmode="numeric" wire:model.lazy="booker_mobile" class="form-control @error('booker_mobile') is-invalid @enderror" style="border-radius:10px;" placeholder="07xxx" oninput="this.value=this.value.replace(/[^0-9+]/g,'')">
                    @error('booker_mobile') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Landline <span class="text-danger">*</span></label>
                    <input type="tel" inputmode="numeric" wire:model.lazy="booker_landline" class="form-control" style="border-radius:10px;" placeholder="01xxx" oninput="this.value=this.value.replace(/[^0-9+]/g,'')">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Email <span class="text-danger">*</span></label>
                    <input type="email" wire:model.lazy="booker_email" class="form-control @error('booker_email') is-invalid @enderror" style="border-radius:10px;" placeholder="caller@email.com">
                    @error('booker_email') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-5">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Address <span class="text-danger">*</span></label>
                    <textarea wire:model.lazy="booker_address" class="form-control @error('booker_address') is-invalid @enderror" rows="2" style="border-radius:10px;" placeholder="Street address..."></textarea>
                    @error('booker_address') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Postcode <span class="text-danger">*</span></label>
                    <input type="text" wire:model.lazy="booker_postcode" class="form-control @error('booker_postcode') is-invalid @enderror" style="border-radius:10px;text-transform:uppercase;" placeholder="SW1A 1AA" oninput="this.value=this.value.toUpperCase()">
                    @error('booker_postcode') <small class="text-danger fw-semibold">{{ $message }}</small> @enderror
                  </div>
                </div>

                {{-- Returning / Referral (always visible, auto-populated) --}}
                <div class="p-3" style="background:#F8F9FB; border-radius:14px; border:1px solid rgba(51,46,158,0.10);">
                  <h6 class="fw-semibold mb-2" style="font-size:0.876rem; color:#5A6080;">Returning / Referral</h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Old Booking Reference</label>
                      <input type="text" wire:model.lazy="old_booking_reference" class="form-control" style="border-radius:10px;" placeholder="Auto-populated from previous bookings">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold mb-1" style="font-size:0.876rem; color:#5A6080;">Previous Booking Type</label>
                      <x-styled-select modelName="previous_booking_type" :placeholder="'Select…'" :optgroup="false" :force-drop-up="true" :options="[
                        ['value' => 'flight', 'label' => 'Flight'],
                        ['value' => 'hotel', 'label' => 'Hotel'],
                        ['value' => 'holiday', 'label' => 'Holidays'],
                        ['value' => 'umrah', 'label' => 'Umrah'],
                        ['value' => 'visa', 'label' => 'Visa'],
                        ['value' => 'transfers', 'label' => 'Transfers'],
                        ['value' => 'excursion', 'label' => 'Excursion'],
                      ]" />
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- ===== TRAVELLERS: Traveller Information ===== --}}
            @if ($this->currentStepId === 'travellers')
              <div wire:key="step-travellers">
                <h6 class="fw-bold mb-3" style="color: var(--to-charcoal); font-size:1.14rem;">
                  <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                  Traveller Information
                </h6>

                {{-- Passenger counters --}}
                <div class="d-flex align-items-stretch mb-4" style="background:#fff;border:1px solid rgba(51,46,158,0.10);border-radius:14px;box-shadow:0 1px 6px rgba(51,46,158,0.06);overflow:visible;">
                  @foreach (['adult'=>['code'=>'ADT','color'=>'#332E9E'],'gbe'=>['code'=>'GBE','color'=>'#D83F87'],'child'=>['code'=>'CNN','color'=>'#D97706'],'infant'=>['code'=>'INF','color'=>'#16A34A']] as $type => $info)
                    @php $count = ${$type.'Count'}; @endphp
                    <div class="d-flex align-items-center gap-2 px-4 py-2 flex-grow-1 {{ !$loop->last ? 'border-end' : '' }}"
                      style="border-color:rgba(51,46,158,0.08)!important; background:{{ $count > 0 ? $info['color'].'0C' : 'transparent' }}; transition:background 0.2s;">
                      <span style="font-size:0.744rem;font-weight:800;letter-spacing:0.07em;color:{{ $count > 0 ? $info['color'] : '#9CA3AF' }};min-width:26px;">{{ $info['code'] }}</span>
                      <button type="button" wire:click="dec('{{ $type }}')"
                        class="btn d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:22px;height:22px;border-radius:50%;padding:0;border:1.5px solid {{ $count > 0 ? $info['color'].'55' : '#D1D5DB' }};background:transparent;color:{{ $count > 0 ? $info['color'] : '#9CA3AF' }};font-size:1.08rem;font-weight:700;line-height:1;">&minus;</button>
                      <span style="font-size:1.2rem;font-weight:800;color:{{ $count > 0 ? $info['color'] : '#374151' }};min-width:14px;text-align:center;">{{ $count }}</span>
                      <button type="button" wire:click="inc('{{ $type }}')"
                        class="btn d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:22px;height:22px;border-radius:50%;padding:0;border:none;background:{{ $info['color'] }};color:#fff;font-size:1.08rem;font-weight:700;line-height:1;">+</button>
                    </div>
                  @endforeach
                  <div class="d-flex align-items-center px-4" style="background:linear-gradient(135deg,#332E9E,#4A45B5);min-width:70px;justify-content:center;">
                    <div class="text-center">
                      <div style="font-size:1.2rem;font-weight:800;color:#fff;line-height:1;">{{ $this->totalPassengers }}</div>
                      <div style="font-size:0.696rem;color:rgba(255,255,255,0.7);letter-spacing:0.06em;text-transform:uppercase;">pax</div>
                    </div>
                  </div>
                </div>

                {{-- Passengers (full width) --}}
                <div>
                    @if (empty($passengers))
                      <div class="text-center py-5" style="background:rgba(51,46,158,0.02); border-radius:14px; border:2px dashed rgba(51,46,158,0.12);">
                        <div style="font-size:2.5rem; opacity:0.25;" class="mb-2"></div>
                        <p class="text-muted small mb-0">Add travellers using the counters above</p>
                      </div>
                    @else
                      @php $seenTypes = []; @endphp
                      <div class="d-flex flex-column gap-3">
                        @foreach ($passengers as $i => $p)
                          @php
                            $ptc        = !empty($p['date_of_birth']) ? $this->computePtc($p['date_of_birth'], $p['type']) : '';
                            $passengerNum = $this->getPassengerNumber($i, $p['type']);
                            $fullName   = $this->passengerTypeFullName($p['type']);
                            $typeColor  = ['adult'=>'#332E9E','gbe'=>'#D83F87','child'=>'#D97706','infant'=>'#16A34A'][$p['type']] ?? '#6B7280';
                            $ageInfo    = $this->computeAgeInfo($p['date_of_birth'] ?? '');
                            $ptcColors  = ['ADT'=>'#332E9E','GBE'=>'#D83F87','CNN'=>'#D97706','INF'=>'#16A34A'];
                            $isFirstOfType = !in_array($p['type'], $seenTypes);
                            $seenTypes[] = $p['type'];
                            $typeCountMap = ['adult' => $adultCount, 'gbe' => $gbeCount, 'child' => $childCount, 'infant' => $infantCount];
                            $typeQty    = (int)($typeCountMap[$p['type']] ?? 0);
                          @endphp
                          <div x-data="{ open: true }" style="background:#fff; border-radius:14px; border:1px solid rgba(51,46,158,0.10); box-shadow:0 2px 10px rgba(51,46,158,0.06); overflow:visible;">

                            {{-- Coloured header - click to collapse --}}
                            <div class="d-flex align-items-center justify-content-between px-4 py-2"
                              style="background:{{ $typeColor }}0D; border-bottom:1px solid {{ $typeColor }}22; cursor:pointer; user-select:none;"
                              @click="open = !open">
                              <div class="d-flex align-items-center gap-2">
                                <span class="badge fw-bold" style="background:{{ $typeColor }};color:#fff;border-radius:7px;font-size:0.864rem;padding:3px 10px;">
                                  {{ $this->passengerTypeLabel($p['type']) }}
                                </span>
                                <span class="fw-semibold" style="font-size:1.056rem;color:#20242B;">{{ $fullName }} {{ $passengerNum }}</span>
                                @if ($ptc)
                                  <span style="background:{{ $ptcColors[$ptc] ?? '#6B7280' }};color:#fff;border-radius:7px;font-size:0.864rem;font-weight:800;padding:3px 10px;letter-spacing:0.04em;">{{ $ptc }}</span>
                                @endif
                                {{-- Name summary when collapsed --}}
                                <span x-show="!open" style="font-size:0.9rem;color:#6B7280;display:none;">
                                  @if (!empty($p['first_name']) || !empty($p['last_name']))
                                    - {{ trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) }}
                                  @endif
                                </span>
                              </div>
                              <div @click.stop>
                                <i :class="open ? 'ph ph-caret-up' : 'ph ph-caret-down'"
                                  style="font-size:1.08rem;color:{{ $typeColor }};cursor:pointer;opacity:0.6;" @click="open = !open"></i>
                              </div>
                            </div>

                            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                              x-transition:enter-start="opacity-0 transform -translate-y-1"
                              x-transition:enter-end="opacity-100 transform translate-y-0"
                              class="px-4 py-3">
                              {{-- Name + Title in one row --}}
                              <div class="row g-2 mb-2">
                                <div class="col-2">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Title</label>
                                  @php
                                    $isMinor = in_array($p['type'], ['child', 'infant']);
                                    $titleOpts = $isMinor
                                      ? [['value'=>'Mstr','label'=>'Mstr'],['value'=>'Miss','label'=>'Miss']]
                                      : [['value'=>'Mr.','label'=>'Mr.'],['value'=>'Ms.','label'=>'Ms.'],['value'=>'Mrs.','label'=>'Mrs.'],['value'=>'Mstr','label'=>'Mstr'],['value'=>'Miss','label'=>'Miss'],['value'=>'Dr.','label'=>'Dr.']];
                                  @endphp
                                  <x-styled-select-sm :modelName="'passengers.' . $i . '.title'" :placeholder="'-'" :optgroup="false" :options="$titleOpts" />
                                  @error("passengers.{$i}.title") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-5">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">First Name <span class="text-danger">*</span></label>
                                  <input type="text" wire:model.lazy="passengers.{{ $i }}.first_name" required
                                    class="form-control form-control-sm @error("passengers.{$i}.first_name") is-invalid @enderror"
                                    style="font-size:0.936rem;border-radius:7px;" placeholder="First name">
                                  @error("passengers.{$i}.first_name") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-5">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Last Name <span class="text-danger">*</span></label>
                                  <input type="text" wire:model.lazy="passengers.{{ $i }}.last_name" required
                                    class="form-control form-control-sm @error("passengers.{$i}.last_name") is-invalid @enderror"
                                    style="font-size:0.936rem;border-radius:7px;" placeholder="Last name">
                                  @error("passengers.{$i}.last_name") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                              </div>

                              {{-- DOB + Passport in one row --}}
                              <div class="row g-2 mb-2">
                                <div class="col-4">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Date of Birth <span class="text-danger">*</span></label>
                                  <x-date-picker modelName="passengers.{{ $i }}.date_of_birth" placeholder="DD/MM/YYYY" :compact="true" />
                                  @error("passengers.{$i}.date_of_birth") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-4">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Passport # <span class="text-danger">*</span></label>
                                  <input type="text" wire:model.lazy="passengers.{{ $i }}.passport_number"
                                    class="form-control form-control-sm @error("passengers.{$i}.passport_number") is-invalid @enderror" style="font-size:0.936rem;border-radius:7px;" placeholder="Passport number">
                                  @error("passengers.{$i}.passport_number") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                                {{-- Age info + PTC conversion (inline, same style) --}}
                                <div class="col-4 d-flex flex-row align-items-end gap-2 pb-1">
                                  @if ($ageInfo['years'] > 0 || $ageInfo['months'] > 0 || $ageInfo['days'] > 0)
                                    <span class="badge" style="background:rgba(51,46,158,0.08);color:#332E9E;font-size:0.816rem;font-weight:600;border-radius:7px;padding:3px 8px;white-space:nowrap;">
                                      {{ $ageInfo['years'] }}y {{ $ageInfo['months'] }}m {{ $ageInfo['days'] }}d
                                    </span>
                                  @endif
                                  @if (!empty($ageInfo['next_ptc']) && !empty($ageInfo['next_ptc_date']))
                                    <span class="badge" style="background:rgba(51,46,158,0.08);color:#332E9E;font-size:0.792rem;font-weight:600;border-radius:7px;padding:3px 8px;white-space:nowrap;display:inline-flex;align-items:center;gap:3px;">
                                      <i class="ph ph-arrow-up-right" style="font-size:0.72rem;"></i>
                                      {{ str_replace(['Child (CNN)','Youth (GBE)','Adult (ADT)'],['CNN','GBE','ADT'],$ageInfo['next_ptc']) }} on {{ $ageInfo['next_ptc_date'] }}
                                    </span>
                                  @endif
                                </div>
                              </div>

                              {{-- Contact Number + E-Ticket --}}
                              <div class="row g-2 mb-2">
                                <div class="col-4">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Contact Number <span class="text-danger">*</span></label>
                                  <input type="tel" inputmode="numeric" wire:model.lazy="passengers.{{ $i }}.contact_number"
                                    class="form-control form-control-sm @error("passengers.{$i}.contact_number") is-invalid @enderror" style="font-size:0.936rem;border-radius:7px;" placeholder="07xxx"
                                    oninput="this.value=this.value.replace(/[^0-9+]/g,'')">
                                  @error("passengers.{$i}.contact_number") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-4">
                                  <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">
                                    E-Ticket #
                                    <span class="text-muted fw-normal" style="font-size:0.72rem;">(issued later)</span>
                                  </label>
                                  <input type="text" wire:model.lazy="passengers.{{ $i }}.e_ticket_number"
                                    class="form-control form-control-sm" style="font-size:0.936rem;border-radius:7px;" placeholder="e.g. 176-1234567890">
                                </div>
                              </div>

                              <div class="d-flex justify-content-end mt-3 pt-2" style="border-top:1px solid rgba(51,46,158,0.06);">
                                <button type="button" wire:click="dec('{{ $p['type'] }}')"
                                  class="btn btn-sm d-flex align-items-center gap-1"
                                  style="font-size:0.84rem;color:#DC2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.15);border-radius:8px;padding:4px 12px;">
                                  <i class="ph ph-trash" style="font-size:0.96rem;"></i>
                                  Remove {{ $fullName }}
                                </button>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    @if($errors->has('passengers'))
                      <small id="pax-error" class="text-danger fw-semibold d-block mt-2">{{ $errors->first('passengers') }}</small>
                    @else
                      <div id="pax-error" style="display:none;"></div>
                    @endif
                </div>
              </div>
            @endif

            {{-- ===== FLIGHT: Flight Information ===== --}}
            @if ($this->currentStepId === 'flight')
              <div wire:key="step-flight">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h6 class="fw-bold mb-0" style="color: var(--to-charcoal); font-size:1.14rem;">
                    <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                    Flight Information
                  </h6>
                  <div class="d-flex align-items-center gap-3">
                    <label class="d-flex align-items-center gap-1 mb-0" style="cursor:pointer;">
                      <div class="form-check form-switch mb-0 ps-0">
                        <input type="checkbox" wire:model.live="flight_atol" role="switch" class="form-check-input ms-0" style="width:28px;height:15px;cursor:pointer;">
                      </div>
                      <span style="font-size:0.84rem;font-weight:700;color:{{ $flight_atol ? '#FF6B35' : '#374151' }};">ATOL</span>
                    </label>
                    <label class="d-flex align-items-center gap-1 mb-0" style="cursor:pointer;">
                      <div class="form-check form-switch mb-0 ps-0">
                        <input type="checkbox" wire:model.live="flight_safi" role="switch" class="form-check-input ms-0" style="width:28px;height:15px;cursor:pointer;">
                      </div>
                      <span style="font-size:0.84rem;font-weight:700;color:{{ $flight_safi ? '#332E9E' : '#374151' }};">SAFI</span>
                    </label>
                  </div>
                </div>


                {{-- Flight segments --}}
                @foreach ($flightSegments as $si => $seg)
                  <div class="mb-3" wire:key="seg-{{ $si }}" style="border-radius:14px; border:1px solid rgba(51,46,158,{{ $si === 0 ? '0.10' : '0.18' }}); overflow:visible;">

                    {{-- Segment header --}}
                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                      style="background:rgba(51,46,158,0.04); border-bottom:1px solid rgba(51,46,158,0.08);">
                      <span class="fw-bold" style="font-size:0.936rem; color:#332E9E;">
                        PNR {{ $si + 1 }}
                        @if (!empty($seg['departure_airport']) && !empty($seg['arrival_airport']))
                          <span class="text-muted fw-normal" style="font-size:0.864rem;"> · {{ strtoupper($seg['departure_airport']) }} → {{ strtoupper($seg['arrival_airport']) }}</span>
                        @endif
                      </span>
                      @if ($si > 0)
                        <button type="button" wire:click="removeFlightSegment({{ $si }})"
                          wire:loading.attr="disabled" wire:target="removeFlightSegment({{ $si }})"
                          class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:7px;font-size:0.84rem;padding:2px 8px;">
                          <span wire:loading.remove wire:target="removeFlightSegment({{ $si }})">Remove</span>
                          <span wire:loading wire:target="removeFlightSegment({{ $si }})" style="font-size:0.78rem;">…</span>
                        </button>
                      @endif
                    </div>

                    <div class="p-3">
                      {{-- Row 1: Locator, Airline Locator, Vendor --}}
                      <div class="row g-2 mb-2">
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Locator <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="flightSegments.{{ $si }}.locator"
                            class="form-control form-control-sm @error("flightSegments.{$si}.locator") is-invalid @enderror" style="border-radius:8px;">
                          @error("flightSegments.{$si}.locator") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Airline Locator <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="flightSegments.{{ $si }}.airline_locator"
                            class="form-control form-control-sm @error("flightSegments.{$si}.airline_locator") is-invalid @enderror" style="border-radius:8px;">
                          @error("flightSegments.{$si}.airline_locator") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Vendor <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'flightSegments.'.$si.'.vendor'" :placeholder="'Select Vendor'" :optgroup="false" :options="$vendorOptions" />
                          @error("flightSegments.{$si}.vendor") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                      </div>

                      {{-- Row 2: Airline, GDS, Cabin, Reservation, Ticket Limit --}}
                      <div class="row g-2 mb-2">
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Airline <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="flightSegments.{{ $si }}.airline"
                            class="form-control form-control-sm @error("flightSegments.{$si}.airline") is-invalid @enderror" style="border-radius:8px;text-transform:uppercase;"
                            placeholder="EK, BA…" maxlength="2" oninput="this.value=this.value.toUpperCase()">
                          @error("flightSegments.{$si}.airline") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">GDS <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'flightSegments.'.$si.'.gds'" :placeholder="'GDS'" :optgroup="false" :options="$gdsOptions" />
                          @error("flightSegments.{$si}.gds") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Cabin <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'flightSegments.'.$si.'.cabin'" :placeholder="'Cabin'" :optgroup="false" :options="[
                            ['value' => 'Economy',         'label' => 'Economy'],
                            ['value' => 'Premium Economy', 'label' => 'Premium Economy'],
                            ['value' => 'Business',        'label' => 'Business'],
                            ['value' => 'First Class',     'label' => 'First Class'],
                          ]" />
                          @error("flightSegments.{$si}.cabin") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Reservation <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'flightSegments.'.$si.'.reservation_status'" :placeholder="'Status'" :optgroup="false" :options="[
                            ['value' => 'Confirmed',  'label' => 'Confirmed'],
                            ['value' => 'Ticketed',   'label' => 'Ticketed'],
                            ['value' => 'Pending',    'label' => 'Pending'],
                            ['value' => 'On Hold',    'label' => 'On Hold'],
                            ['value' => 'Cancelled',  'label' => 'Cancelled'],
                          ]" />
                          @error("flightSegments.{$si}.reservation_status") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Ticket Limit <span class="text-danger">*</span></label>
                          <x-date-picker :modelName="'flightSegments.'.$si.'.ticket_issue_limit'" placeholder="Ticket Limit" :compact="true" :isDateTime="true" />
                          @error("flightSegments.{$si}.ticket_issue_limit") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                      </div>

                      {{-- Row 3: Airports + dates --}}
                      <div class="row g-2 mb-3">
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Dep. Airport <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="flightSegments.{{ $si }}.departure_airport" required
                            class="form-control form-control-sm @error("flightSegments.{$si}.departure_airport") is-invalid @enderror" maxlength="3" style="border-radius:8px;text-transform:uppercase;"
                            oninput="this.value=this.value.toUpperCase().slice(0,3)">
                          @error("flightSegments.{$si}.departure_airport") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Arr. Airport <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="flightSegments.{{ $si }}.arrival_airport" required
                            class="form-control form-control-sm @error("flightSegments.{$si}.arrival_airport") is-invalid @enderror" maxlength="3" style="border-radius:8px;text-transform:uppercase;"
                            oninput="this.value=this.value.toUpperCase().slice(0,3)">
                          @error("flightSegments.{$si}.arrival_airport") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Flight Type</label>
                          <x-styled-select :modelName="'flightSegments.'.$si.'.flight_type'" :placeholder="''" :optgroup="false" :live="true" :options="[['value'=>'return','label'=>'Return'],['value'=>'one_way','label'=>'One Way']]" />
                          @error("flightSegments.{$si}.flight_type") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Dep. Date <span class="text-danger">*</span></label>
                          <x-date-picker :modelName="'flightSegments.'.$si.'.departure_date'" placeholder="Departure" :compact="true" />
                          @error("flightSegments.{$si}.departure_date") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        @if (($seg['flight_type'] ?? 'return') !== 'one_way')
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Return Date <span class="text-danger">*</span></label>
                          <x-date-picker :modelName="'flightSegments.'.$si.'.return_date'" placeholder="Return" :compact="true" />
                          @error("flightSegments.{$si}.return_date") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        @endif
                      </div>

                      {{-- Passenger Cost/Sold under this PNR --}}
                      @if(count($this->passengers) > 0)
                        <div class="mt-2 pt-2" style="border-top:1px solid rgba(51,46,158,.08);">
                          <div class="d-flex align-items-center gap-2 mb-1">
                            <span style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;">Pricing</span>
                            <span style="font-size:0.744rem;color:#C4C9D6;flex:1;font-weight:500;">Cost · Sold</span>
                          </div>
                          <div class="d-flex flex-column gap-1">
                            @foreach($this->passengers as $pi => $p)
                              @php
                                $ptcCodes = ['adult'=>'ADT','gbe'=>'GBE','child'=>'CNN','infant'=>'INF'];
                                $ptcColors = ['adult'=>'#332E9E','gbe'=>'#D83F87','child'=>'#D97706','infant'=>'#16A34A'];
                                $t = $p['type'] ?? 'adult';
                                $num = 1;
                                for ($j=0; $j<$pi; $j++) { if(($this->passengers[$j]['type']??'')===$t) $num++; }
                                $pCode = ($ptcCodes[$t] ?? 'PAX').' '.$num;
                                $pColor = $ptcColors[$t] ?? '#94A3B8';
                              @endphp
                              <div class="d-flex align-items-center gap-2">
                                <span style="font-size:0.78rem;font-weight:700;color:{{ $pColor }};min-width:46px;">{{ $pCode }}</span>
                                <input type="number" wire:model.lazy="flightSegments.{{ $si }}.passenger_costs.{{ $pi }}.cost"
                                  step="0.01" min="0" class="form-control form-control-sm"
                                  style="font-size:0.816rem;border-radius:6px;padding:2px 6px;width:80px;flex-shrink:0;" placeholder="Cost">
                                <input type="number" wire:model.lazy="flightSegments.{{ $si }}.passenger_costs.{{ $pi }}.sold"
                                  step="0.01" min="0" class="form-control form-control-sm"
                                  style="font-size:0.816rem;border-radius:6px;padding:2px 6px;width:80px;flex-shrink:0;" placeholder="Sold">
                              </div>
                            @endforeach
                          </div>
                        </div>
                      @endif

                      {{-- PNR for this segment --}}
                      <div>
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">PNR Details</label>
                        <div class="d-flex gap-2 align-items-start">
                          <textarea wire:model.lazy="flightSegments.{{ $si }}.pnr" rows="14"
                            placeholder="Paste PNR here - RP/LONBA1234..."
                            class="form-control form-control-sm flex-grow-1"
                            style="border-radius:10px; font-family:monospace; font-size:0.9rem; resize:vertical; min-height:220px;"></textarea>
                          <button type="button" class="btn btn-sm fw-semibold flex-shrink-0"
                            style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;font-size:0.864rem;white-space:nowrap;padding:6px 12px;">
                            Fetch PNR
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach

                {{-- Quick-add buttons --}}
                <div class="d-flex gap-2 mt-2 flex-wrap">
                  <button type="button" wire:click="addFlightSegment"
                    wire:loading.attr="disabled" wire:target="addFlightSegment"
                    class="btn btn-sm d-flex align-items-center gap-1"
                    style="background:rgba(51,46,158,0.07);color:#332E9E;border:1px solid rgba(51,46,158,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                    <span wire:loading.remove wire:target="addFlightSegment"><i class="ph ph-plus" style="font-size:0.96rem;"></i> Add PNR</span>
                    <span wire:loading wire:target="addFlightSegment" style="font-size:0.816rem;">Adding…</span>
                  </button>
                  @if (in_array($booking_type, ['holiday', 'umrah']))
                    <button type="button" wire:click="addHotel"
                      wire:loading.attr="disabled" wire:target="addHotel"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(124,58,237,0.07);color:#7C3AED;border:1px solid rgba(124,58,237,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                      <span wire:loading.remove wire:target="addHotel"><i class="ph ph-plus" style="font-size:0.96rem;"></i> Add Hotel</span>
                      <span wire:loading wire:target="addHotel" style="font-size:0.816rem;">Adding…</span>
                    </button>
                    <button type="button" wire:click="addVisa"
                      wire:loading.attr="disabled" wire:target="addVisa"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(16,163,74,0.07);color:#16A34A;border:1px solid rgba(16,163,74,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                      <span wire:loading.remove wire:target="addVisa"><i class="ph ph-plus" style="font-size:0.96rem;"></i> Add Visa</span>
                      <span wire:loading wire:target="addVisa" style="font-size:0.816rem;">Adding…</span>
                    </button>
                    <button type="button" wire:click="addPickup"
                      wire:loading.attr="disabled" wire:target="addPickup"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(51,46,158,0.07);color:#332E9E;border:1px solid rgba(51,46,158,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                      <span wire:loading.remove wire:target="addPickup"><i class="ph ph-van" style="font-size:0.96rem;"></i> Add Transfer</span>
                      <span wire:loading wire:target="addPickup" style="font-size:0.816rem;">Adding…</span>
                    </button>
                    <button type="button" wire:click="$set('showExcursionStep', true)"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(255,107,53,0.07);color:#FF6B35;border:1px solid rgba(255,107,53,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                      <i class="ph ph-binoculars" style="font-size:0.96rem;"></i> Add Excursion
                    </button>
                  @endif
                </div>

                {{-- Inline Excursion (added via button) --}}
                @if ($showExcursionStep)
                  <div class="mt-3" wire:key="inline-excursion" style="border-radius:14px;border:1px solid rgba(255,107,53,0.18);overflow:visible;">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:rgba(255,107,53,0.06);border-bottom:1px solid rgba(255,107,53,0.12);">
                      <span class="fw-bold" style="font-size:0.936rem;color:#FF6B35;">
                        <i class="ph ph-binoculars" style="font-size:0.96rem;"></i> Excursion
                      </span>
                      <button type="button" wire:click="$set('showExcursionStep', false)"
                        class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:6px;font-size:0.78rem;padding:2px 8px;">Remove</button>
                    </div>
                    <div class="p-3">
                      <div class="row g-2 mb-2">
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Activity Name <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="excursion_name" class="form-control form-control-sm @error('excursion_name') is-invalid @enderror" style="border-radius:8px;" placeholder="Desert Safari, Nile Cruise...">
                          @error('excursion_name') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Destination <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="excursion_destination" class="form-control form-control-sm @error('excursion_destination') is-invalid @enderror" style="border-radius:8px;" placeholder="Dubai, Luxor...">
                          @error('excursion_destination') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Date <span class="text-danger">*</span></label>
                          <x-date-picker modelName="excursion_date" placeholder="Date" :compact="true" />
                          @error('excursion_date') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Status</label>
                          <x-styled-select modelName="excursion_status" :placeholder="''" :optgroup="false" :options="[
                            ['value'=>'confirmed','label'=>'Confirmed'],
                            ['value'=>'pending','label'=>'Pending'],
                            ['value'=>'cancelled','label'=>'Cancelled'],
                          ]" />
                        </div>
                      </div>
                      <div class="row g-2 mb-2">
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Cost (£)</label>
                          <input type="number" wire:model.lazy="excursion_actual_cost" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Sold (£)</label>
                          <input type="number" wire:model.lazy="excursion_selling_price" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Notes</label>
                          <textarea wire:model.lazy="excursion_notes" rows="1" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Special requirements..."></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            @endif

            {{-- ===== HOTEL: Hotel Information ===== --}}
            @if ($this->currentStepId === 'hotel')
              <div wire:key="step-hotel">
                <h6 class="fw-bold mb-3" style="color: var(--to-charcoal); font-size:1.14rem;">
                  <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                  Hotel Information
                </h6>

                @foreach ($hotels as $hi => $hotel)
                  @php
                    $roomCount = count($hotel['rooms'] ?? []);
                    $hid = 'hotel-' . $hi;
                  @endphp
                  <div wire:key="{{ $hid }}" class="mb-3" style="border-radius:14px; border:1px solid rgba(51,46,158,0.10); overflow:visible;">

                    {{-- Hotel header --}}
                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                      style="background:{{ $hi === 0 ? 'rgba(124,58,237,0.04)' : 'rgba(124,58,237,0.08)' }}; border-bottom:1px solid rgba(124,58,237,0.08);">
                      <span class="fw-bold" style="font-size:0.936rem; color:#7C3AED;">
                        Hotel {{ $hi + 1 }}
                        @if (!empty($hotel['hotel_name']))
                          <span class="text-muted fw-normal" style="font-size:0.864rem;"> - {{ $hotel['hotel_name'] }}</span>
                        @endif
                      </span>
                      <button type="button" wire:click="removeHotel({{ $hi }})"
                        wire:loading.attr="disabled" wire:target="removeHotel({{ $hi }})"
                        class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:7px;font-size:0.84rem;padding:2px 10px;">
                        <span wire:loading.remove wire:target="removeHotel({{ $hi }})">Remove</span>
                        <span wire:loading wire:target="removeHotel({{ $hi }})" style="font-size:0.78rem;">…</span>
                      </button>
                    </div>

                    <div class="p-3">
                      {{-- Hotel-level fields --}}
                      <div class="row g-2 mb-2">
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Hotel Name <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="hotels.{{ $hi }}.hotel_name" class="form-control form-control-sm @error("hotels.{$hi}.hotel_name") is-invalid @enderror" style="border-radius:8px;" placeholder="Hotel name">
                          @error("hotels.{$hi}.hotel_name") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">City <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="hotels.{{ $hi }}.city" class="form-control form-control-sm @error("hotels.{$hi}.city") is-invalid @enderror" style="border-radius:8px;" placeholder="City">
                          @error("hotels.{$hi}.city") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Status <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'hotels.'.$hi.'.status'" :placeholder="''" :optgroup="false" :options="[
                            ['value' => 'confirmed', 'label' => 'Confirmed'],
                            ['value' => 'on_holding', 'label' => 'On Holding'],
                            ['value' => 'cancelled', 'label' => 'Cancelled'],
                          ]" />
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Check In <span class="text-danger">*</span></label>
                          <x-date-picker :modelName="'hotels.'.$hi.'.check_in'" placeholder="Check In" :compact="true" />
                          @error("hotels.{$hi}.check_in") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Check Out <span class="text-danger">*</span></label>
                          <x-date-picker :modelName="'hotels.'.$hi.'.check_out'" placeholder="Check Out" :compact="true" />
                          @error("hotels.{$hi}.check_out") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                      </div>
                      <div class="row g-2 mb-2">
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Actual Cost (&pound;)</label>
                          <input type="number" wire:model.lazy="hotels.{{ $hi }}.actual_cost" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Selling Price (&pound;)</label>
                          <input type="number" wire:model.lazy="hotels.{{ $hi }}.selling_price" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#5A6080;">Number of Rooms</label>
                          <input type="number" wire:model.lazy="hotels.{{ $hi }}.number_of_rooms" class="form-control form-control-sm" min="1" style="border-radius:8px;" value="1">
                        </div>
                      </div>

                      {{-- Rooms --}}
                      <div class="mt-3 pt-3" style="border-top:1px solid rgba(124,58,237,0.12);">
                        <span class="fw-semibold" style="font-size:0.876rem; color:#7C3AED;">Rooms ({{ $roomCount }})</span>
                        @foreach ($hotel['rooms'] as $ri => $room)
                          <div wire:key="{{ $hid }}-room-{{ $ri }}" class="p-3 mt-2" style="background:rgba(124,58,237,0.03); border-radius:12px; border:1px solid rgba(124,58,237,0.08);">
                            <div class="fw-bold mb-2" style="font-size:0.864rem; color:#5A6080;">Room {{ $ri + 1 }}</div>
                            <div class="row g-2">
                              <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:0.792rem; color:#5A6080;">Room Type</label>
                                <input type="text" wire:model.lazy="hotels.{{ $hi }}.rooms.{{ $ri }}.room_type" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Double">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:0.792rem; color:#5A6080;">Occupants</label>
                                <input type="number" wire:model.lazy="hotels.{{ $hi }}.rooms.{{ $ri }}.occupants" class="form-control form-control-sm" min="1" style="border-radius:7px;">
                              </div>
                              <div class="col-md-5">
                                <label class="form-label fw-semibold mb-1" style="font-size:0.792rem; color:#5A6080;">Meal Basis</label>
                                <x-styled-select :modelName="'hotels.'.$hi.'.rooms.'.$ri.'.meal_basis'" :placeholder="''" :optgroup="false" :options="[
                                  ['value' => 'room_only', 'label' => 'Room Only'],
                                  ['value' => 'breakfast', 'label' => 'Breakfast'],
                                  ['value' => 'half_board', 'label' => 'Half Board'],
                                  ['value' => 'full_board', 'label' => 'Full Board'],
                                  ['value' => 'all_inclusive', 'label' => 'All Inclusive'],
                                ]" />
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                @endforeach

                {{-- Add Another Hotel button --}}
                <button type="button" wire:click="addHotel"
                  wire:loading.attr="disabled" wire:target="addHotel"
                  class="btn btn-sm d-flex align-items-center gap-2 w-100 justify-content-center"
                  style="background:rgba(124,58,237,0.06); color:#7C3AED; border:1.5px dashed rgba(124,58,237,0.25); border-radius:12px; padding:10px; font-size:0.936rem; font-weight:600;">
                  <span wire:loading.remove wire:target="addHotel"><i class="ph ph-plus-circle" style="font-size:1.2rem;"></i> Add Another Hotel</span>
                  <span wire:loading wire:target="addHotel" style="font-size:0.864rem;">Adding…</span>
                </button>
              </div>
            @endif

            {{-- ===== TRANSFERS: Transfer Information ===== --}}
            @if ($this->currentStepId === 'transfers')
              <div wire:key="step-transfers">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h6 class="fw-bold mb-0" style="color: var(--to-charcoal); font-size:1.14rem;">
                    <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                    Transfers
                  </h6>
                  <div class="d-flex gap-2">
                    <button type="button" wire:click="addPickup"
                      wire:loading.attr="disabled" wire:target="addPickup"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(51,46,158,0.08);color:#332E9E;border:none;border-radius:8px;font-size:0.864rem;font-weight:600;padding:5px 14px;">
                      <span wire:loading.remove wire:target="addPickup"><i class="ph ph-arrow-up" style="font-size:1.02rem;"></i> Add Pickup</span>
                      <span wire:loading wire:target="addPickup" style="font-size:0.816rem;">Adding…</span>
                    </button>
                    <button type="button" wire:click="addDropoff"
                      wire:loading.attr="disabled" wire:target="addDropoff"
                      class="btn btn-sm d-flex align-items-center gap-1"
                      style="background:rgba(216,63,135,0.08);color:#D83F87;border:none;border-radius:8px;font-size:0.864rem;font-weight:600;padding:5px 14px;">
                      <span wire:loading.remove wire:target="addDropoff"><i class="ph ph-arrow-down" style="font-size:1.02rem;"></i> Add Dropoff</span>
                      <span wire:loading wire:target="addDropoff" style="font-size:0.816rem;">Adding…</span>
                    </button>
                  </div>
                </div>

                {{-- Side-by-side Pickups & Dropoffs --}}
                <div class="row g-3">
                  {{-- Pickups Column --}}
                  <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <i class="ph ph-arrow-up-right" style="color:#332E9E;font-size:1.2rem;"></i>
                      <span class="fw-bold" style="font-size:0.936rem; color:#332E9E;">Pickups</span>
                      <span class="badge px-2" style="background:rgba(51,46,158,0.08);color:#332E9E;font-size:0.78rem;">{{ count($transferPickups) }}</span>
                    </div>
                    @foreach ($transferPickups as $pi => $pickup)
                      <div wire:key="pickup-{{ $pi }}" class="mb-2" style="border-radius:12px; border:1px solid rgba(51,46,158,0.12); overflow:visible;">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:rgba(51,46,158,0.04); border-bottom:1px solid rgba(51,46,158,0.06);">
                          <span class="fw-semibold" style="font-size:0.876rem; color:#332E9E;">
                            <i class="ph ph-map-pin-line" style="font-size:0.9rem;"></i> Pickup {{ $pi + 1 }}
                          </span>
                          <button type="button" wire:click="removePickup({{ $pi }})"
                            wire:loading.attr="disabled" wire:target="removePickup({{ $pi }})"
                            class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:6px;font-size:0.78rem;padding:2px 8px;">
                            <span wire:loading.remove wire:target="removePickup({{ $pi }})">Remove</span>
                            <span wire:loading wire:target="removePickup({{ $pi }})" style="font-size:0.78rem;">…</span>
                          </button>
                        </div>
                        <div class="p-3">
                          <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Pickup Location</label>
                            <input type="text" wire:model.lazy="transferPickups.{{ $pi }}.location" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Airport / Hotel / Address">
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-7">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Date &amp; Time</label>
                              <x-date-picker :modelName="'transferPickups.'.$pi.'.date_time'" placeholder="Date & Time" :compact="true" :isDateTime="true" />
                            </div>
                            <div class="col-5">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Flight #</label>
                              <input type="text" wire:model.lazy="transferPickups.{{ $pi }}.flight_number" class="form-control form-control-sm" style="border-radius:7px;font-weight:600;" placeholder="e.g. EK001">
                            </div>
                          </div>
                          <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Route</label>
                            <input type="text" wire:model.lazy="transferPickups.{{ $pi }}.route" class="form-control form-control-sm" style="border-radius:7px;" placeholder="e.g. LHR T3 → Central London">
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-6">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Vehicle Type</label>
                              <x-styled-select-sm modelName="transferPickups.{{ $pi }}.vehicle_type" :options="[['value'=>'','label'=>'-'],['value'=>'Minicab','label'=>'Minicab'],['value'=>'Executive Car','label'=>'Executive Car'],['value'=>'Minibus','label'=>'Minibus'],['value'=>'Coach','label'=>'Coach'],['value'=>'Limo','label'=>'Limousine'],['value'=>'Other','label'=>'Other']]" placeholder="-" />
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Supplier</label>
                              <input type="text" wire:model.lazy="transferPickups.{{ $pi }}.supplier" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Company name">
                            </div>
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Cost (£)</label>
                              <input type="number" wire:model.lazy="transferPickups.{{ $pi }}.actual_cost" step="0.01" min="0" class="form-control form-control-sm" style="border-radius:7px;" placeholder="0.00">
                            </div>
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Selling (£)</label>
                              <input type="number" wire:model.lazy="transferPickups.{{ $pi }}.selling_price" step="0.01" min="0" class="form-control form-control-sm" style="border-radius:7px;" placeholder="0.00">
                            </div>
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Status</label>
                              <x-styled-select-sm modelName="transferPickups.{{ $pi }}.status" :options="[['value'=>'confirmed','label'=>'Confirmed'],['value'=>'pending','label'=>'Pending'],['value'=>'cancelled','label'=>'Cancelled']]" placeholder="Status" />
                            </div>
                          </div>
                          <div>
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Notes</label>
                            <input type="text" wire:model.lazy="transferPickups.{{ $pi }}.notes" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Any additional notes">
                          </div>
                        </div>
                      </div>
                    @endforeach
                    @if (empty($transferPickups))
                      <div class="text-center py-4" style="background:rgba(51,46,158,0.02); border-radius:12px; border:2px dashed rgba(51,46,158,0.1);">
                        <i class="ph ph-arrow-up-right" style="font-size:1.8rem;color:rgba(51,46,158,0.2);"></i>
                        <div style="font-size:0.864rem;color:#475569;margin-top:4px;">No pickups added yet</div>
                      </div>
                    @endif
                  </div>

                  {{-- Dropoffs Column --}}
                  <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <i class="ph ph-arrow-down-right" style="color:#D83F87;font-size:1.2rem;"></i>
                      <span class="fw-bold" style="font-size:0.936rem; color:#D83F87;">Dropoffs</span>
                      <span class="badge px-2" style="background:rgba(216,63,135,0.08);color:#D83F87;font-size:0.78rem;">{{ count($transferDropoffs) }}</span>
                    </div>
                    @foreach ($transferDropoffs as $di => $dropoff)
                      <div wire:key="dropoff-{{ $di }}" class="mb-2" style="border-radius:12px; border:1px solid rgba(216,63,135,0.12); overflow:visible;">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:rgba(216,63,135,0.04); border-bottom:1px solid rgba(216,63,135,0.06);">
                          <span class="fw-semibold" style="font-size:0.876rem; color:#D83F87;">
                            <i class="ph ph-map-pin" style="font-size:0.9rem;"></i> Dropoff {{ $di + 1 }}
                          </span>
                          <button type="button" wire:click="removeDropoff({{ $di }})"
                            wire:loading.attr="disabled" wire:target="removeDropoff({{ $di }})"
                            class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:6px;font-size:0.78rem;padding:2px 8px;">
                            <span wire:loading.remove wire:target="removeDropoff({{ $di }})">Remove</span>
                            <span wire:loading wire:target="removeDropoff({{ $di }})" style="font-size:0.78rem;">…</span>
                          </button>
                        </div>
                        <div class="p-3">
                          <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Dropoff Location</label>
                            <input type="text" wire:model.lazy="transferDropoffs.{{ $di }}.location" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Airport / Hotel / Address">
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-7">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Date &amp; Time</label>
                              <x-date-picker :modelName="'transferDropoffs.'.$di.'.date_time'" placeholder="Date & Time" :compact="true" :isDateTime="true" />
                            </div>
                            <div class="col-5">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Flight #</label>
                              <input type="text" wire:model.lazy="transferDropoffs.{{ $di }}.flight_number" class="form-control form-control-sm" style="border-radius:7px;font-weight:600;" placeholder="e.g. EK002">
                            </div>
                          </div>
                          <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Route</label>
                            <input type="text" wire:model.lazy="transferDropoffs.{{ $di }}.route" class="form-control form-control-sm" style="border-radius:7px;" placeholder="e.g. Central London → LHR T5">
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-6">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Vehicle Type</label>
                              <x-styled-select-sm modelName="transferDropoffs.{{ $di }}.vehicle_type" :options="[['value'=>'','label'=>'-'],['value'=>'Minicab','label'=>'Minicab'],['value'=>'Executive Car','label'=>'Executive Car'],['value'=>'Minibus','label'=>'Minibus'],['value'=>'Coach','label'=>'Coach'],['value'=>'Limo','label'=>'Limousine'],['value'=>'Other','label'=>'Other']]" placeholder="-" />
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Supplier</label>
                              <input type="text" wire:model.lazy="transferDropoffs.{{ $di }}.supplier" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Company name">
                            </div>
                          </div>
                          <div class="row g-2 mb-2">
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Cost (£)</label>
                              <input type="number" wire:model.lazy="transferDropoffs.{{ $di }}.actual_cost" step="0.01" min="0" class="form-control form-control-sm" style="border-radius:7px;" placeholder="0.00">
                            </div>
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Selling (£)</label>
                              <input type="number" wire:model.lazy="transferDropoffs.{{ $di }}.selling_price" step="0.01" min="0" class="form-control form-control-sm" style="border-radius:7px;" placeholder="0.00">
                            </div>
                            <div class="col-4">
                              <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Status</label>
                              <x-styled-select-sm modelName="transferDropoffs.{{ $di }}.status" :options="[['value'=>'confirmed','label'=>'Confirmed'],['value'=>'pending','label'=>'Pending'],['value'=>'cancelled','label'=>'Cancelled']]" placeholder="Status" />
                            </div>
                          </div>
                          <div>
                            <label class="form-label fw-semibold mb-1" style="font-size:0.816rem;color:#5A6080;">Notes</label>
                            <input type="text" wire:model.lazy="transferDropoffs.{{ $di }}.notes" class="form-control form-control-sm" style="border-radius:7px;" placeholder="Any additional notes">
                          </div>
                        </div>
                      </div>
                    @endforeach
                    @if (empty($transferDropoffs))
                      <div class="text-center py-4" style="background:rgba(216,63,135,0.02); border-radius:12px; border:2px dashed rgba(216,63,135,0.1);">
                        <i class="ph ph-arrow-down-right" style="font-size:1.8rem;color:rgba(216,63,135,0.2);"></i>
                        <div style="font-size:0.864rem;color:#475569;margin-top:4px;">No dropoffs added yet</div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endif

            {{-- ===== VISA: Umrah Visa Information ===== --}}
            @if ($this->currentStepId === 'visa')
              <div wire:key="step-visa">
                <h6 class="fw-bold mb-3" style="color: var(--to-charcoal); font-size:1.14rem;">
                  <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                  Visa Information
                </h6>

                @foreach ($visas as $vi => $visa)
                  <div wire:key="visa-{{ $vi }}" class="mb-3" style="border-radius:14px;border:1px solid rgba(51,46,158,0.10);overflow:visible;">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:rgba(51,46,158,0.04);border-bottom:1px solid rgba(51,46,158,0.08);">
                      <span class="fw-bold" style="font-size:0.936rem;color:#332E9E;">
                        Visa {{ $vi + 1 }}
                        @if(!empty($visa['passenger_name']))
                          <span class="text-muted fw-normal" style="font-size:0.864rem;">· {{ $visa['passenger_name'] }}</span>
                        @endif
                      </span>
                      <button type="button" wire:click="removeVisa({{ $vi }})"
                        wire:loading.attr="disabled" wire:target="removeVisa({{ $vi }})"
                        class="btn btn-sm" style="background:rgba(220,38,38,0.08);color:#DC2626;border:none;border-radius:6px;font-size:0.78rem;padding:2px 8px;">
                        <span wire:loading.remove wire:target="removeVisa({{ $vi }})">Remove</span>
                        <span wire:loading wire:target="removeVisa({{ $vi }})" style="font-size:0.78rem;">…</span>
                      </button>
                    </div>
                    <div class="p-3">
                      <div class="row g-2 mb-2">
                        <div class="col-md-4">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Passenger <span class="text-danger">*</span></label>
                          @php
                            $passengerNameOpts = collect($passengers)->map(function($p, $pi) {
                              $labels = ['adult'=>'Adult','gbe'=>'Youth','child'=>'Child','infant'=>'Infant'];
                              $num = 1;
                              for ($j=0; $j<$pi; $j++) { if(($passengers[$j]['type']??'')===$p['type']) $num++; }
                              $name = trim(($p['first_name']??'').' '.($p['last_name']??''));
                              $display = $name ?: ($labels[$p['type']] ?? 'Pax').' '.$num;
                              return ['value' => $display, 'label' => $display];
                            })->values()->toArray();
                          @endphp
                          <x-styled-select :modelName="'visas.'.$vi.'.passenger_name'" :placeholder="'Select'" :optgroup="false" :options="$passengerNameOpts" />
                          @error("visas.{$vi}.passenger_name") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Visa Type <span class="text-danger">*</span></label>
                          <x-styled-select :modelName="'visas.'.$vi.'.visa_type'" :placeholder="''" :optgroup="false" :options="[
                            ['value'=>'umrah',    'label'=>'Umrah'],
                            ['value'=>'tourist',  'label'=>'Tourist'],
                            ['value'=>'business', 'label'=>'Business'],
                            ['value'=>'transit',  'label'=>'Transit'],
                            ['value'=>'student',  'label'=>'Student'],
                          ]" />
                          @error("visas.{$vi}.visa_type") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Status</label>
                          <x-styled-select :modelName="'visas.'.$vi.'.status'" :placeholder="''" :optgroup="false" :options="[
                            ['value'=>'pending',   'label'=>'Pending'],
                            ['value'=>'applied',   'label'=>'Applied'],
                            ['value'=>'approved',  'label'=>'Approved'],
                            ['value'=>'rejected',  'label'=>'Rejected'],
                            ['value'=>'collected', 'label'=>'Collected'],
                          ]" />
                          @error("visas.{$vi}.status") <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                        </div>
                      </div>
                      <div class="row g-2 mb-2">
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">App. Reference</label>
                          <input type="text" wire:model.lazy="visas.{{ $vi }}.visa_reference" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Ref #">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Visa Number</label>
                          <input type="text" wire:model.lazy="visas.{{ $vi }}.visa_number" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Visa #">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Applied</label>
                          <x-date-picker :modelName="'visas.'.$vi.'.application_date'" placeholder="Date" :compact="true" />
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Issued</label>
                          <x-date-picker :modelName="'visas.'.$vi.'.issue_date'" placeholder="Date" :compact="true" />
                        </div>
                        <div class="col-md-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Expiry</label>
                          <x-date-picker :modelName="'visas.'.$vi.'.expiry_date'" placeholder="Date" :compact="true" />
                        </div>
                      </div>
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Cost (&pound;)</label>
                          <input type="number" wire:model.lazy="visas.{{ $vi }}.actual_cost" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Sold (&pound;)</label>
                          <input type="number" wire:model.lazy="visas.{{ $vi }}.selling_price" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Notes</label>
                          <input type="text" wire:model.lazy="visas.{{ $vi }}.notes" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Notes...">
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach

                <div class="d-flex gap-2 mt-1">
                  <button type="button" wire:click="addVisa"
                    wire:loading.attr="disabled" wire:target="addVisa"
                    class="btn btn-sm d-flex align-items-center gap-1"
                    style="background:rgba(51,46,158,0.07);color:#332E9E;border:1px solid rgba(51,46,158,0.18);border-radius:8px;font-size:0.864rem;font-weight:600;padding:4px 12px;">
                    <span wire:loading.remove wire:target="addVisa"><i class="ph ph-plus" style="font-size:0.96rem;"></i> Add Visa</span>
                    <span wire:loading wire:target="addVisa" style="font-size:0.816rem;">Adding…</span>
                  </button>
                </div>
              </div>
            @endif

            {{-- ===== EXCURSION: Excursion Information ===== --}}
            @if ($this->currentStepId === 'excursion')
              <div wire:key="step-excursion">
                <h6 class="fw-bold mb-3" style="color: var(--to-charcoal); font-size:1.14rem;">
                  <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                  Excursion Details
                </h6>

                <div style="border-radius:14px;border:1px solid rgba(51,46,158,0.10);overflow:visible;">
                  <div class="px-3 py-2" style="background:rgba(51,46,158,0.04);border-bottom:1px solid rgba(51,46,158,0.08);">
                    <span class="fw-bold" style="font-size:0.936rem;color:#332E9E;">Excursion Details</span>
                  </div>
                  <div class="p-3">
                    <div class="row g-2 mb-2">
                      <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Activity Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model.lazy="excursion_name" class="form-control form-control-sm @error('excursion_name') is-invalid @enderror" style="border-radius:8px;" placeholder="Desert Safari, Nile Cruise...">
                        @error('excursion_name') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Destination <span class="text-danger">*</span></label>
                        <input type="text" wire:model.lazy="excursion_destination" class="form-control form-control-sm @error('excursion_destination') is-invalid @enderror" style="border-radius:8px;" placeholder="Dubai, Luxor...">
                        @error('excursion_destination') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Date <span class="text-danger">*</span></label>
                        <x-date-picker modelName="excursion_date" placeholder="Date" :compact="true" />
                        @error('excursion_date') <small class="text-danger d-block" style="font-size:0.78rem;">{{ $message }}</small> @enderror
                      </div>
                      <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Status</label>
                        <x-styled-select modelName="excursion_status" :placeholder="''" :optgroup="false" :options="[
                          ['value'=>'confirmed',  'label'=>'Confirmed'],
                          ['value'=>'pending',    'label'=>'Pending'],
                          ['value'=>'cancelled',  'label'=>'Cancelled'],
                        ]" />
                      </div>
                    </div>
                    <div class="row g-2 mb-2">
                      <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Duration</label>
                        <input type="text" wire:model.lazy="excursion_duration" class="form-control form-control-sm" style="border-radius:8px;" placeholder="4 hrs">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Supplier</label>
                        <input type="text" wire:model.lazy="excursion_supplier" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Operator name">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Meeting Point</label>
                        <input type="text" wire:model.lazy="excursion_meeting_point" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Hotel lobby...">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Reference</label>
                        <input type="text" wire:model.lazy="excursion_reference" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Ref #">
                      </div>
                    </div>
                    <div class="row g-2 mb-2">
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Cost (&pound;)</label>
                        <input type="number" wire:model.lazy="excursion_actual_cost" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Sold (&pound;)</label>
                        <input type="number" wire:model.lazy="excursion_selling_price" class="form-control form-control-sm" step="0.01" min="0" style="border-radius:8px;" placeholder="0.00">
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Inclusions</label>
                        <textarea wire:model.lazy="excursion_inclusions" rows="2" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Transfers, meals, guide..."></textarea>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Notes</label>
                        <textarea wire:model.lazy="excursion_notes" rows="2" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Special requirements..."></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- ===== PAYMENT: Payment & Wrap-up ===== --}}
            @if ($this->currentStepId === 'payment')
              <div wire:key="step-payment">
                <h6 class="fw-bold mb-3" style="color: var(--to-charcoal); font-size:1.14rem;">
                  <span style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border-radius:8px;padding:2px 10px;margin-right:8px;font-size:0.9rem;">STEP {{ $step }}</span>
                  Payment &amp; Wrap-up
                </h6>

                <div class="row g-3">
                  {{-- Left: Payment Method (radio) + Documents --}}
                  <div class="col-md-8">

                    {{-- ── Payment Method (radio buttons) ── --}}
                    <div class="mb-3" style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);padding:22px;">
                      <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin-bottom:14px;">Payment Method</div>
                      @php
                        $methods = [
                          'epay_debit'      => ['label'=>'Epay Debit',       'icon'=>'ph-credit-card',      'color'=>'#332E9E'],
                          'epay_credit'     => ['label'=>'Epay Credit',      'icon'=>'ph-credit-card',      'color'=>'#7C3AED'],
                          'bank_transfer'   => ['label'=>'Bank Transfer',    'icon'=>'ph-bank',             'color'=>'#16A34A'],
                          'debit_card'      => ['label'=>'Debit Card',       'icon'=>'ph-credit-card',      'color'=>'#0EA5E9'],
                          'credit_card'     => ['label'=>'Credit Card',      'icon'=>'ph-credit-card',      'color'=>'#D97706'],
                          'amex'            => ['label'=>'AMEX',             'icon'=>'ph-credit-card',      'color'=>'#1E40AF'],
                          'klarna'          => ['label'=>'Klarna',           'icon'=>'ph-storefront',       'color'=>'#FF69B4'],
                          'superpay'        => ['label'=>'SuperPay',         'icon'=>'ph-lightning',        'color'=>'#DC2626'],
                          'clearpay'        => ['label'=>'ClearPay',         'icon'=>'ph-arrows-clockwise', 'color'=>'#047857'],
                          'stripe'          => ['label'=>'Stripe',           'icon'=>'ph-lightning',        'color'=>'#6366F1'],
                          'previous_booking'=> ['label'=>'Prev. Booking',    'icon'=>'ph-clock-clockwise',  'color'=>'#94A3B8'],
                          'refund'          => ['label'=>'Refund',           'icon'=>'ph-arrow-u-up-left',  'color'=>'#DC2626'],
                        ];
                      @endphp
                      <div class="row g-2">
                        @foreach($methods as $val => $m)
                          <div class="col-md-3 col-6">
                            <label style="cursor:pointer;display:block;">
                              <input type="radio" wire:model.live="payment_method" value="{{ $val }}" style="display:none;" class="pm-radio">
                              <div class="pm-card px-3 py-2 d-flex align-items-center gap-2"
                                style="border-radius:10px;border:1.5px solid {{ $payment_method === $val ? $m['color'] : 'rgba(51,46,158,.10)' }};background:{{ $payment_method === $val ? $m['color'].'10' : '#fff' }};transition:all .15s;cursor:pointer;">
                                <i class="ph {{ $m['icon'] }}" style="font-size:1.08rem;color:{{ $payment_method === $val ? $m['color'] : '#94A3B8' }};flex-shrink:0;"></i>
                                <span style="font-size:0.888rem;font-weight:{{ $payment_method === $val ? '700' : '500' }};color:{{ $payment_method === $val ? $m['color'] : '#374151' }};">{{ $m['label'] }}</span>
                              </div>
                            </label>
                          </div>
                        @endforeach
                      </div>
                      @error('payment_method')
                        <div style="font-size:0.84rem;color:#DC2626;margin-top:10px;font-weight:600;">{{ $message }}</div>
                      @else
                        @if(!$payment_method)
                          <div style="font-size:0.84rem;color:#475569;margin-top:10px;">Select a payment method above</div>
                        @endif
                      @enderror
                    </div>

                    {{-- ── Card Details (only for direct card payments, not Epay) ── --}}
                    @if(in_array($payment_method, ['amex','credit_card','debit_card']))
                    <div class="mb-3" style="border-radius:12px;border:1.5px solid rgba(51,46,158,.12);overflow:hidden;">
                      <div style="padding:10px 16px;background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.08);">
                        <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#332E9E;">Card Details</span>
                      </div>
                      <div class="p-3">
                        <div class="mb-2">
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Card Number <span class="text-danger">*</span></label>
                          <input type="text" wire:model.lazy="card_number" class="form-control form-control-sm" style="border-radius:8px;letter-spacing:.1em;font-family:monospace;" placeholder="•••• •••• •••• ••••" maxlength="19" oninput="this.value=this.value.replace(/[^0-9 ]/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19)">
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Expiry (MM/YY) <span class="text-danger">*</span></label>
                            <input type="text" wire:model.lazy="card_expiry" class="form-control form-control-sm" style="border-radius:8px;" placeholder="MM/YY" maxlength="5" oninput="this.value=this.value.replace(/[^0-9/]/g,'')">
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">CVV <span class="text-danger">*</span></label>
                            <input type="password" wire:model.lazy="card_cvv" class="form-control form-control-sm" style="border-radius:8px;" placeholder="•••" maxlength="4" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                          </div>
                        </div>
                        <div>
                          <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Billing Address <span class="text-danger">*</span></label>
                          <textarea wire:model.lazy="billing_address" class="form-control form-control-sm" rows="2" style="border-radius:8px;" placeholder="Full billing address..."></textarea>
                        </div>
                      </div>
                    </div>
                    @endif

                    {{-- CC Charges (auto-applied for card payments, editable) --}}
                    @if(in_array($payment_method, ['amex','credit_card','debit_card']))
                    <div class="mb-3" style="border-radius:12px;border:1.5px solid rgba(220,38,38,.15);overflow:hidden;">
                      <div style="padding:10px 16px;background:rgba(220,38,38,.04);border-bottom:1px solid rgba(220,38,38,.10);display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#DC2626;">CC Charges</span>
                        @if($cc_charges > 0)
                          <span style="font-size:0.78rem;font-weight:800;color:#DC2626;">–£{{ number_format($cc_charges,2) }}</span>
                        @endif
                      </div>
                      <div class="p-3">
                        <div class="row g-2 align-items-end">
                          <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Rate (%)</label>
                            <input type="number" wire:model.live="cc_charge_rate" class="form-control form-control-sm" style="border-radius:8px;" placeholder="e.g. 1.5" min="0" max="10" step="0.1">
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Amount (£)</label>
                            <input type="number" wire:model.live="cc_charges" class="form-control form-control-sm" style="border-radius:8px;" placeholder="0.00" min="0" step="0.01">
                          </div>
                        </div>
                        <div style="font-size:0.72rem;color:#475569;margin-top:6px;">Auto-calculated from sold price. Adjust if needed.</div>
                      </div>
                    </div>
                    @endif

                    {{-- Booking Notes --}}
                    <div class="mb-3">
                      <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Booking Notes <span style="color:#DC2626;">*</span></label>
                      <textarea wire:model="mandatory_comment" rows="3" class="form-control form-control-sm" style="border-radius:8px;font-size:0.936rem;" placeholder="Add a note about this booking — e.g. customer preferences, special requests, payment arrangement details…"></textarea>
                      @error('mandatory_comment') <div style="font-size:0.816rem;color:#DC2626;margin-top:3px;">{{ $message }}</div> @enderror
                      <div style="font-size:0.72rem;color:#475569;margin-top:4px;">Required. Logged with your name and timestamp.</div>
                    </div>

                    {{-- Documents --}}
                    <div class="mb-3">
                      <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Documents</label>
                      @foreach ($documents as $i => $doc)
                        <div class="d-flex gap-2 align-items-center mb-2">
                          <input type="file" wire:model.lazy="documents.{{ $i }}" class="form-control form-control-sm" style="max-width:220px;border-radius:8px;">
                          <div style="width:130px;flex-shrink:0;">
                            <x-styled-select-sm :modelName="'document_types.' . $i" :placeholder="'Type'" :optgroup="false" :options="[
                              ['value' => 'e_ticket',      'label' => 'E-Ticket'],
                              ['value' => 'hotel_voucher', 'label' => 'Hotel Voucher'],
                              ['value' => 'passport',      'label' => 'Passport'],
                              ['value' => 'visa',          'label' => 'Visa'],
                              ['value' => 'itinerary',     'label' => 'Itinerary'],
                              ['value' => 'invoice',       'label' => 'Invoice'],
                              ['value' => 'other',         'label' => 'Other'],
                            ]" />
                          </div>
                          <button type="button" wire:click="removeDocument({{ $i }})" class="btn btn-sm btn-outline-danger px-2 py-0" style="border-radius:6px;">&times;</button>
                        </div>
                      @endforeach
                      <button type="button" wire:click="addDocument" class="btn btn-sm fw-semibold mt-1" style="background:rgba(51,46,158,0.08);color:#332E9E;border:none;border-radius:8px;font-size:0.864rem;">+ Add Document</button>
                    </div>
                  </div>

                  {{-- Right: Cost & Margins --}}
                  <div class="col-md-4">
                    @php
                      // Aggregate pax costs by type across all flight segments
                      $typeOrder  = ['adult','gbe','child','infant'];
                      $typeLabels = ['adult'=>'Adult','gbe'=>'GBE','child'=>'Child','infant'=>'Infant'];
                      $typeColors = ['adult'=>'#332E9E','gbe'=>'#D83F87','child'=>'#D97706','infant'=>'#16A34A'];
                      $typeBg     = ['adult'=>'rgba(51,46,158,.08)','gbe'=>'rgba(216,63,135,.08)','child'=>'rgba(217,119,6,.08)','infant'=>'rgba(22,163,74,.08)'];
                      $typeGroups = [];
                      foreach ($this->passengers as $pi => $pax) {
                          $t = $pax['type'] ?? 'adult';
                          if (!isset($typeGroups[$t])) $typeGroups[$t] = ['count'=>0,'cost'=>0,'sold'=>0];
                          $typeGroups[$t]['count']++;
                          foreach ($flightSegments as $seg) {
                              $pc = $seg['passenger_costs'][$pi] ?? [];
                              $typeGroups[$t]['cost'] += (float)($pc['cost'] ?? 0);
                              $typeGroups[$t]['sold'] += (float)($pc['sold'] ?? 0);
                          }
                      }
                      $flightCost  = array_sum(array_column($typeGroups, 'cost'));
                      $flightSold  = array_sum(array_column($typeGroups, 'sold'));
                      $hotelCost   = collect($hotels)->sum(fn($h)=>(float)($h['actual_cost']??0));
                      $hotelSold   = collect($hotels)->sum(fn($h)=>(float)($h['selling_price']??0));
                      $visaCost    = collect($visas)->sum(fn($v)=>(float)($v['actual_cost']??0));
                      $visaSold    = collect($visas)->sum(fn($v)=>(float)($v['selling_price']??0));
                      $excCost     = (float)($excursion_actual_cost ?: 0);
                      $excSold     = (float)($excursion_selling_price ?: 0);
                      $ccAmt       = (float)($cc_charges ?: 0);
                    @endphp
                    <div style="border-radius:14px;border:1px solid rgba(51,46,158,.10);overflow:hidden;background:#fff;box-shadow:0 2px 12px rgba(51,46,158,.06);">

                      {{-- Panel header --}}
                      <div style="padding:11px 16px;background:linear-gradient(135deg,rgba(51,46,158,.06),rgba(99,102,241,.03));border-bottom:1px solid rgba(51,46,158,.08);display:flex;align-items:center;gap:8px;">
                        <i class="ph ph-currency-circle-dollar" style="color:#FF6B35;font-size:1.2rem;"></i>
                        <span style="font-size:0.864rem;font-weight:800;color:#0F172A;letter-spacing:-.01em;">Cost &amp; Margins</span>
                      </div>

                      {{-- ── FLIGHT section (aggregated by pax type) ── --}}
                      @if(in_array($booking_type, ['flight','holiday','umrah']))
                        <div style="display:flex;align-items:center;gap:7px;padding:7px 14px;background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.07);">
                          <i class="ph ph-airplane-tilt" style="font-size:0.864rem;color:#332E9E;"></i>
                          <span style="font-size:0.684rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:#332E9E;">Flight</span>
                        </div>
                        {{-- column header --}}
                        <div style="display:grid;grid-template-columns:1fr 36px 72px 72px;padding:4px 14px;background:rgba(248,250,255,.9);border-bottom:1px solid rgba(51,46,158,.06);">
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;">Type</span>
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;text-align:center;">Pax</span>
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;text-align:right;">Cost</span>
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;text-align:right;">Sold</span>
                        </div>
                        @foreach ($typeOrder as $t)
                          @php
                            $tData  = $typeGroups[$t] ?? ['count'=>0,'cost'=>0,'sold'=>0];
                            $tColor = $typeColors[$t];
                            $tBg    = $typeBg[$t];
                            $tMgn   = $tData['sold'] - $tData['cost'];
                          @endphp
                          @if ($tData['count'] > 0)
                            <div style="display:grid;grid-template-columns:1fr 36px 72px 72px;align-items:center;padding:7px 14px;border-bottom:1px solid rgba(51,46,158,.05);border-left:3px solid {{ $tColor }};">
                              <div>
                                <span style="font-size:0.768rem;font-weight:700;color:{{ $tColor }};">{{ $typeLabels[$t] }}</span>
                                @if($tMgn != 0)
                                  <span style="font-size:0.648rem;font-weight:700;color:{{ $tMgn >= 0 ? '#16A34A' : '#DC2626' }};display:block;">{{ $tMgn >= 0 ? '+' : '' }}£{{ number_format($tMgn,2) }}</span>
                                @endif
                              </div>
                              <span style="font-size:0.768rem;font-weight:700;color:{{ $tColor }};text-align:center;">{{ $tData['count'] }}</span>
                              <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($tData['cost'],2) }}</span>
                              <span style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($tData['sold'],2) }}</span>
                            </div>
                          @endif
                        @endforeach
                        @if ($this->safiTax > 0)
                          <div style="display:grid;grid-template-columns:1fr 36px 72px 72px;align-items:center;padding:6px 14px;background:rgba(51,46,158,.03);border-bottom:1px solid rgba(51,46,158,.06);border-left:3px solid #332E9E;">
                            <span style="font-size:0.744rem;font-weight:700;color:#332E9E;">SAFI</span>
                            <span style="font-size:0.744rem;font-weight:700;color:#332E9E;text-align:center;">{{ $this->nonInfantPassengerCount }}</span>
                            <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($this->safiTax,2) }}</span>
                            <span style="font-size:0.816rem;font-weight:600;color:#475569;text-align:right;">—</span>
                          </div>
                        @endif
                        @if ($this->atolTax > 0)
                          <div style="display:grid;grid-template-columns:1fr 36px 72px 72px;align-items:center;padding:6px 14px;background:rgba(255,107,53,.04);border-bottom:1px solid rgba(255,107,53,.10);border-left:3px solid #FF6B35;">
                            <span style="font-size:0.744rem;font-weight:700;color:#FF6B35;">ATOL</span>
                            <span style="font-size:0.744rem;font-weight:700;color:#FF6B35;text-align:center;">{{ $this->nonInfantPassengerCount }}</span>
                            <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($this->atolTax,2) }}</span>
                            <span style="font-size:0.816rem;font-weight:600;color:#475569;text-align:right;">—</span>
                          </div>
                        @endif
                        {{-- Flight totals row --}}
                        @php $fTotCost = $flightCost + $this->atolSafiTax; $fTotSold = $flightSold; @endphp
                        <div style="display:grid;grid-template-columns:1fr 36px 72px 72px;align-items:center;padding:6px 14px;background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.08);">
                          <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
                          <span></span>
                          <span style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($fTotCost,2) }}</span>
                          <span style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($fTotSold,2) }}</span>
                        </div>
                      @endif

                      {{-- ── HOTEL section ── --}}
                      @if (count($hotels) > 0)
                        @php
                          $allHotelCost = collect($hotels)->sum(fn($h) => (float)($h['actual_cost'] ?? 0));
                          $allHotelSold = collect($hotels)->sum(fn($h) => (float)($h['selling_price'] ?? 0));
                        @endphp
                        <div style="display:flex;align-items:center;gap:7px;padding:7px 14px;background:rgba(124,58,237,.04);border-bottom:1px solid rgba(124,58,237,.08);border-top:1px solid rgba(124,58,237,.08);">
                          <i class="ph ph-buildings" style="font-size:0.864rem;color:#7C3AED;"></i>
                          <span style="font-size:0.684rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:#7C3AED;">Hotel</span>
                        </div>
                        {{-- column header --}}
                        <div style="display:grid;grid-template-columns:1fr 72px 72px;padding:4px 14px;background:rgba(248,250,255,.9);border-bottom:1px solid rgba(124,58,237,.06);">
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;">Hotel</span>
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;text-align:right;">Cost</span>
                          <span style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;text-align:right;">Sold</span>
                        </div>
                        @foreach ($hotels as $hi => $h)
                          @php $hCost = (float)($h['actual_cost']??0); $hSold = (float)($h['selling_price']??0); $hMgn = $hSold - $hCost; @endphp
                          <div style="display:grid;grid-template-columns:1fr 72px 72px;align-items:center;padding:7px 14px;border-bottom:1px solid rgba(124,58,237,.05);border-left:3px solid #7C3AED;">
                            <div>
                              <span style="font-size:0.768rem;font-weight:600;color:#1E293B;display:block;">{{ $h['hotel_name'] ?: 'Hotel '.($hi+1) }}</span>
                              @if($hMgn != 0)<span style="font-size:0.648rem;font-weight:700;color:{{ $hMgn >= 0 ? '#16A34A' : '#DC2626' }};">{{ $hMgn >= 0 ? '+' : '' }}£{{ number_format($hMgn,2) }}</span>@endif
                            </div>
                            <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($hCost,2) }}</span>
                            <span style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($hSold,2) }}</span>
                          </div>
                        @endforeach
                        {{-- Hotel totals row --}}
                        <div style="display:grid;grid-template-columns:1fr 72px 72px;align-items:center;padding:6px 14px;background:rgba(124,58,237,.04);border-bottom:1px solid rgba(124,58,237,.08);">
                          <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
                          <span style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($allHotelCost,2) }}</span>
                          <span style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($allHotelSold,2) }}</span>
                        </div>
                      @endif

                      {{-- ── VISA section ── --}}
                      @if (count($visas) > 0)
                        <div style="display:flex;align-items:center;gap:7px;padding:7px 14px;background:rgba(22,163,74,.04);border-bottom:1px solid rgba(22,163,74,.08);border-top:1px solid rgba(22,163,74,.08);">
                          <i class="ph ph-identification-card" style="font-size:0.864rem;color:#16A34A;"></i>
                          <span style="font-size:0.684rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:#16A34A;">Visa ({{ count($visas) }})</span>
                        </div>
                        @foreach ($visas as $vi => $v)
                          @php $vCost = (float)($v['actual_cost']??0); $vSold = (float)($v['selling_price']??0); $vMgn = $vSold - $vCost; @endphp
                          <div style="display:grid;grid-template-columns:1fr 72px 72px;align-items:center;padding:7px 14px;border-bottom:1px solid rgba(22,163,74,.05);border-left:3px solid #16A34A;">
                            <div>
                              <span style="font-size:0.768rem;font-weight:600;color:#1E293B;">Visa {{ $vi + 1 }}</span>
                              @if($vMgn != 0)<span style="font-size:0.648rem;font-weight:700;color:{{ $vMgn >= 0 ? '#16A34A' : '#DC2626' }};display:block;">{{ $vMgn >= 0 ? '+' : '' }}£{{ number_format($vMgn,2) }}</span>@endif
                            </div>
                            <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($vCost,2) }}</span>
                            <span style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($vSold,2) }}</span>
                          </div>
                        @endforeach
                      @else
                        @php $visaCost = 0; $visaSold = 0; @endphp
                      @endif

                      {{-- ── EXCURSION section ── --}}
                      @if ($excCost > 0 || $excSold > 0)
                        @php $excMgn = $excSold - $excCost; @endphp
                        <div style="display:flex;align-items:center;gap:7px;padding:7px 14px;background:rgba(255,107,53,.04);border-bottom:1px solid rgba(255,107,53,.08);border-top:1px solid rgba(255,107,53,.08);">
                          <i class="ph ph-binoculars" style="font-size:0.864rem;color:#FF6B35;"></i>
                          <span style="font-size:0.684rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:#FF6B35;">Excursion</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 72px 72px;align-items:center;padding:7px 14px;border-bottom:1px solid rgba(255,107,53,.06);border-left:3px solid #FF6B35;">
                          <div>
                            <span style="font-size:0.768rem;font-weight:600;color:#1E293B;">{{ $excursion_name ?: 'Excursion' }}</span>
                            @if($excMgn != 0)<span style="font-size:0.648rem;font-weight:700;color:{{ $excMgn >= 0 ? '#16A34A' : '#DC2626' }};display:block;">{{ $excMgn >= 0 ? '+' : '' }}£{{ number_format($excMgn,2) }}</span>@endif
                          </div>
                          <span style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($excCost,2) }}</span>
                          <span style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($excSold,2) }}</span>
                        </div>
                      @endif

                      {{-- ── Totals + CC + Dual Margin ── --}}
                      @php
                        $totCost  = $flightCost + $this->atolSafiTax + $hotelCost + $visaCost + $excCost;
                        $totSold  = $flightSold + $hotelSold + $visaSold + $excSold;
                        $grossMgn = $totSold - $totCost;
                        $netMgn   = $grossMgn - $ccAmt;
                        $netPct   = $totSold > 0 ? round(($netMgn / $totSold) * 100, 1) : 0;
                      @endphp
                      <div style="padding:14px 16px;border-top:2px solid rgba(51,46,158,.08);">
                        <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                          <span style="font-size:0.744rem;font-weight:700;color:#475569;">Total Cost</span>
                          <span style="font-size:0.84rem;font-weight:700;color:#1E293B;">£{{ number_format($totCost,2) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                          <span style="font-size:0.744rem;font-weight:700;color:#475569;">Total Sold</span>
                          <span style="font-size:0.84rem;font-weight:700;color:#1E293B;">£{{ number_format($totSold,2) }}</span>
                        </div>

                        {{-- CC Charges row (auto-applied for card payments) --}}
                        @if($ccAmt > 0)
                          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-radius:8px;background:rgba(220,38,38,.06);border:1px solid rgba(220,38,38,.15);margin-bottom:10px;">
                            <div>
                              <span style="font-size:0.744rem;font-weight:700;color:#DC2626;">CC Charges</span>
                              @if($cc_charge_rate)
                                <span style="font-size:0.672rem;color:#475569;margin-left:4px;">({{ $cc_charge_rate }}%)</span>
                              @endif
                            </div>
                            <span style="font-size:0.864rem;font-weight:800;color:#DC2626;">–£{{ number_format($ccAmt,2) }}</span>
                          </div>
                        @endif

                        {{-- Margin without CC --}}
                        @if($ccAmt > 0)
                          <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 10px;border-radius:8px;background:rgba(51,46,158,.04);border:1px solid rgba(51,46,158,.08);margin-bottom:8px;">
                            <span style="font-size:0.72rem;font-weight:700;color:#475569;">Margin (excl. CC)</span>
                            <span style="font-size:0.84rem;font-weight:700;color:{{ $grossMgn >= 0 ? '#16A34A' : '#DC2626' }};">£{{ number_format($grossMgn,2) }}</span>
                          </div>
                        @endif

                        {{-- Net Margin box --}}
                        <div style="padding:14px;border-radius:12px;{{ $netMgn >= 0 ? 'background:linear-gradient(135deg,rgba(22,163,74,.12),rgba(22,163,74,.05));border:2px solid rgba(22,163,74,.22);' : 'background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(220,38,38,.05));border:2px solid rgba(220,38,38,.22);' }}">
                          <div style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:{{ $netMgn >= 0 ? '#15803D' : '#DC2626' }};margin-bottom:2px;">{{ $ccAmt > 0 ? 'Net Margin (incl. CC)' : 'Total Margin' }}</div>
                          <div style="font-size:1.6rem;font-weight:800;color:{{ $netMgn >= 0 ? '#16A34A' : '#DC2626' }};line-height:1;letter-spacing:-.02em;">£{{ number_format($netMgn,2) }}</div>
                          <div style="font-size:0.744rem;font-weight:700;color:{{ $netMgn >= 0 ? '#16A34A' : '#DC2626' }};margin-top:3px;opacity:.8;">{{ $netPct }}% margin</div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- Navigation buttons --}}
            <div class="d-flex justify-content-between mt-4 pt-3" style="border-top:1px solid rgba(51,46,158,0.08);">
              <div class="d-flex gap-2">
                @if ($step > 1)
                  <button type="button" @mousedown.prevent @click="$wire.prevStep()" class="btn btn-outline-secondary btn-sm fw-semibold px-3" style="border-radius:10px;">&larr; Previous</button>
                @endif
              </div>
              <div class="d-flex gap-2">
                @if ($step < $totalSteps)
                  <button type="button"
                    @mousedown.prevent
                    @click="
                      const stepId = '{{ $this->currentStepId }}';
                      if (stepId === 'travellers') {
                        const pax = ($wire.adultCount||0) + ($wire.gbeCount||0) + ($wire.childCount||0) + ($wire.infantCount||0);
                        if (pax === 0) {
                          document.getElementById('pax-error').style.display = 'block';
                          return;
                        }
                        document.getElementById('pax-error').style.display = 'none';
                      }
                      $wire.nextStep();
                    "
                    wire:loading.attr="disabled" wire:target="nextStep"
                    class="btn btn-sm fw-bold px-4"
                    style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:10px;box-shadow:0 3px 12px rgba(51,46,158,0.25);">
                    <span wire:loading.remove wire:target="nextStep">Next &rarr;</span>
                    <span wire:loading wire:target="nextStep" style="font-size:0.9rem;">Checking…</span>
                  </button>
                @else
                  <button type="submit" class="btn btn-sm fw-bold px-4"
                    style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;box-shadow:0 3px 12px rgba(255,107,53,0.35);">
                    Save Booking
                  </button>
                @endif
              </div>
            </div>

          </div>
        </form>
      @endif
    </div>
  </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
