<div>
  <div class="card border-0" style="border-radius:24px;background:linear-gradient(135deg,rgba(255,255,255,0.95) 0%,rgba(255,255,255,0.88) 100%);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(51,46,158,.12),0 2px 8px rgba(51,46,158,.06);border:1px solid rgba(255,255,255,.6);">
    <div class="card-body p-4">

      @if (session()->has('success'))
        <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
      @endif
      @if (session()->has('error'))
        <div class="alert alert-danger border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('error') }}</div>
      @endif

      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h6 class="fw-bold mb-1" style="color:var(--to-charcoal);font-size:1.14rem;">GDS Options</h6>
          <p class="mb-0" style="font-size:0.864rem;color:#475569;">Add or remove GDS options that appear in the booking flight form.</p>
        </div>
        <span class="badge" style="background:rgba(51,46,158,.08);color:#332E9E;font-size:0.864rem;">{{ count($gdsOptions) }} GDS(s)</span>
      </div>

      {{-- Existing GDS list --}}
      <div class="d-flex flex-column gap-2 mb-4">
        @foreach($gdsOptions as $i => $g)
          <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:10px;">
            <div class="d-flex align-items-center gap-3">
              <span class="fw-semibold" style="font-size:0.936rem;color:#20242B;">{{ $g['label'] }}</span>
              <span class="badge" style="font-size:0.78rem;background:rgba(51,46,158,.06);color:#475569;font-family:monospace;">{{ $g['value'] }}</span>
            </div>
            <button type="button" wire:click="removeGds({{ $i }})"
              wire:loading.attr="disabled" wire:target="removeGds({{ $i }})"
              class="btn btn-sm" style="background:rgba(220,38,38,.06);color:#DC2626;border:none;border-radius:6px;font-size:0.816rem;padding:4px 10px;">
              <span wire:loading.remove wire:target="removeGds({{ $i }})">Remove</span>
              <span wire:loading wire:target="removeGds({{ $i }})" style="font-size:0.78rem;">...</span>
            </button>
          </div>
        @endforeach
      </div>

      {{-- Add new GDS form --}}
      <div class="p-3" style="background:rgba(51,46,158,.03);border-radius:14px;border:1.5px dashed rgba(51,46,158,.15);">
        <h6 class="fw-semibold mb-3" style="font-size:0.912rem;color:#332E9E;">Add New GDS</h6>
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Display Name</label>
            <input type="text" wire:model.live="newGdsLabel" class="form-control form-control-sm" style="border-radius:8px;" placeholder="e.g. Amadeus">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem;color:#5A6080;">Value (stored)</label>
            <input type="text" wire:model.live="newGdsValue" class="form-control form-control-sm" style="border-radius:8px;font-family:monospace;" placeholder="Auto-filled & uppercased">
          </div>
          <div class="col-md-3">
            <button type="button" wire:click="addGds"
              wire:loading.attr="disabled" wire:target="addGds"
              class="btn btn-sm fw-semibold w-100"
              style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:8px;font-size:0.9rem;padding:7px 14px;">
              <span wire:loading.remove wire:target="addGds">Add GDS</span>
              <span wire:loading wire:target="addGds">Adding...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
