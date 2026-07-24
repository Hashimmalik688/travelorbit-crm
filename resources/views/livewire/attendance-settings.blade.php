<div>
  @if (session()->has('success'))
    <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.95rem;">{{ session('success') }}</div>
  @endif
  @if (session()->has('error'))
    <div class="alert alert-danger border-0 py-2 px-3 mb-3" style="font-size:0.95rem;">{{ session('error') }}</div>
  @endif

  <div class="row g-4">
    {{-- ── Timing ── --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h6 class="fw-bold mb-0" style="font-size:1.05rem;">Office Hours</h6>
        </div>
        <div class="card-body">
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="att-enabled" wire:model="enabled">
            <label class="form-check-label" for="att-enabled">Attendance enabled</label>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" style="font-size:0.85rem;">Office start time</label>
              <input type="time" wire:model="officeStartTime" class="form-control form-control-sm">
              @error('officeStartTime') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:0.85rem;">Late after</label>
              <input type="time" wire:model="lateTime" class="form-control form-control-sm">
              @error('lateTime') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:0.85rem;">Shift length (hours)</label>
              <input type="number" min="1" max="24" wire:model="shiftDurationHours" class="form-control form-control-sm">
              @error('shiftDurationHours') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:0.85rem;">Buffer (hours)</label>
              <input type="number" min="0" max="12" wire:model="bufferHours" class="form-control form-control-sm">
              @error('bufferHours') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" id="att-weekend" wire:model="allowWeekend">
            <label class="form-check-label" for="att-weekend">Allow weekend attendance</label>
          </div>

          <p class="mt-3 mb-3" style="font-size:0.8rem;color:#94a3b8;">
            Check-in is allowed from <strong>{{ $bufferHours }}h before</strong> the start time until the shift + buffer ends.
            Anyone checking in after <strong>“Late after”</strong> is marked <em>late</em>.
          </p>

          <button type="button" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings"
                  class="btn btn-primary btn-sm">
            <span wire:loading.remove wire:target="saveSettings"><i class="ph ph-check me-1"></i>Save settings</span>
            <span wire:loading wire:target="saveSettings">Saving…</span>
          </button>
        </div>
      </div>
    </div>

    {{-- ── Holidays ── --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="fw-bold mb-0" style="font-size:1.05rem;">Company Holidays</h6>
          <span class="badge bg-label-primary">{{ count($holidays) }}</span>
        </div>
        <div class="card-body">
          <p style="font-size:0.82rem;color:#64748b;">
            A day added here is a holiday for everyone — attendance isn’t required and it’s never counted absent.
          </p>

          <div class="d-flex flex-column gap-2 mb-3">
            @forelse ($holidays as $i => $h)
              <div class="d-flex align-items-center justify-content-between px-3 py-2"
                   style="background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:10px;">
                <div>
                  <span class="fw-semibold" style="font-size:0.9rem;">{{ $h['name'] }}</span>
                  <span class="badge bg-label-secondary ms-1" style="font-size:0.72rem;">{{ \Carbon\Carbon::parse($h['date'])->format('D, M j, Y') }}</span>
                </div>
                <button type="button" wire:click="removeHoliday({{ $i }})" wire:loading.attr="disabled"
                        class="btn btn-sm" style="background:rgba(220,38,38,.06);color:#DC2626;border:none;border-radius:6px;font-size:0.8rem;padding:3px 10px;">
                  Remove
                </button>
              </div>
            @empty
              <div class="text-center py-3" style="color:#94a3b8;font-size:0.85rem;">No holidays set.</div>
            @endforelse
          </div>

          <div class="p-3" style="background:rgba(51,46,158,.03);border-radius:14px;border:1.5px dashed rgba(51,46,158,.15);">
            <div class="row g-2 align-items-end">
              <div class="col-5">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;color:#5A6080;">Date</label>
                <input type="date" wire:model="newHolidayDate" class="form-control form-control-sm">
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;color:#5A6080;">Name</label>
                <input type="text" wire:model="newHolidayName" class="form-control form-control-sm" placeholder="e.g. Eid">
              </div>
              <div class="col-3">
                <button type="button" wire:click="addHoliday" wire:loading.attr="disabled" wire:target="addHoliday"
                        class="btn btn-primary btn-sm w-100">
                  <span wire:loading.remove wire:target="addHoliday">Add</span>
                  <span wire:loading wire:target="addHoliday">…</span>
                </button>
              </div>
              @error('newHolidayDate') <div class="col-12"><small class="text-danger">{{ $message }}</small></div> @enderror
              @error('newHolidayName') <div class="col-12"><small class="text-danger">{{ $message }}</small></div> @enderror
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
