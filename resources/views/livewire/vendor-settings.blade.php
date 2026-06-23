<div>
  <div class="card border-0" style="border-radius:24px;background:linear-gradient(135deg,rgba(255,255,255,0.95) 0%,rgba(255,255,255,0.88) 100%);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(51,46,158,.12),0 2px 8px rgba(51,46,158,.06);border:1px solid rgba(255,255,255,.6);">
    <div class="card-body p-4">

      @if (session()->has('success'))
        <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:.82rem;">{{ session('success') }}</div>
      @endif
      @if (session()->has('error'))
        <div class="alert alert-danger border-0 py-2 px-3 mb-3" style="font-size:.82rem;">{{ session('error') }}</div>
      @endif

      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h6 class="fw-bold mb-1" style="color:var(--to-charcoal);font-size:.95rem;">Flight Vendors</h6>
          <p class="mb-0" style="font-size:.72rem;color:#94A3B8;">Add or remove vendor options that appear in the booking flight form.</p>
        </div>
        <span class="badge" style="background:rgba(51,46,158,.08);color:#332E9E;font-size:.72rem;">{{ count($vendors) }} vendor(s)</span>
      </div>

      {{-- Existing vendors list --}}
      <div class="d-flex flex-column gap-2 mb-4">
        @foreach($vendors as $i => $v)
          <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:10px;">
            <div class="d-flex align-items-center gap-3">
              <span class="fw-semibold" style="font-size:.78rem;color:#20242B;">{{ $v['label'] }}</span>
              <span class="badge" style="font-size:.65rem;background:rgba(51,46,158,.06);color:#64748B;font-family:monospace;">{{ $v['value'] }}</span>
            </div>
            <button type="button" wire:click="removeVendor({{ $i }})"
              wire:loading.attr="disabled" wire:target="removeVendor({{ $i }})"
              class="btn btn-sm" style="background:rgba(220,38,38,.06);color:#DC2626;border:none;border-radius:6px;font-size:.68rem;padding:4px 10px;">
              <span wire:loading.remove wire:target="removeVendor({{ $i }})">Remove</span>
              <span wire:loading wire:target="removeVendor({{ $i }})" style="font-size:.65rem;">...</span>
            </button>
          </div>
        @endforeach
      </div>

      {{-- Add new vendor form --}}
      <div class="p-3" style="background:rgba(51,46,158,.03);border-radius:14px;border:1.5px dashed rgba(51,46,158,.15);">
        <h6 class="fw-semibold mb-3" style="font-size:.76rem;color:#332E9E;">Add New Vendor</h6>
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label fw-semibold mb-1" style="font-size:.7rem;color:#5A6080;">Display Name</label>
            <input type="text" wire:model.live="newVendorLabel" class="form-control form-control-sm" style="border-radius:8px;" placeholder="e.g. Expedia">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold mb-1" style="font-size:.7rem;color:#5A6080;">Value (stored)</label>
            <input type="text" wire:model.live="newVendorValue" class="form-control form-control-sm" style="border-radius:8px;font-family:monospace;" placeholder="Auto-filled from name">
          </div>
          <div class="col-md-3">
            <button type="button" wire:click="addVendor"
              wire:loading.attr="disabled" wire:target="addVendor"
              class="btn btn-sm fw-semibold w-100"
              style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:8px;font-size:.75rem;padding:7px 14px;">
              <span wire:loading.remove wire:target="addVendor">Add Vendor</span>
              <span wire:loading wire:target="addVendor">Adding...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
