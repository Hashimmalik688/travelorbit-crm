<div>
<style>
.ip-row { display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:10px;border:1px solid rgba(51,46,158,.07);margin-bottom:8px;background:#fff;transition:all .15s; }
.ip-row:hover { border-color:rgba(51,46,158,.15);background:#F8FAFF; }
.ip-inp { border-radius:10px;border:1.5px solid rgba(51,46,158,.12);padding:.45rem .75rem;font-size:0.996rem;background:#fff;color:#1E293B;transition:border-color .15s; }
.ip-inp:focus { border-color:#332E9E;box-shadow:0 0 0 3px rgba(51,46,158,.08);outline:none; }
</style>

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <div style="font-size:0.864rem;color:#475569;margin-bottom:4px;">
      <a href="{{ route('dashboard') }}" style="color:#475569;text-decoration:none;">Dashboard</a> ›
      <a href="{{ route('settings') }}" style="color:#475569;text-decoration:none;">Settings</a> › IP Whitelist
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;margin:0;">IP Whitelist</h1>
    <div style="font-size:0.888rem;color:#475569;margin-top:3px;">Restrict system access to specific IP addresses or networks</div>
  </div>
  {{-- Enable/disable toggle --}}
  <div class="d-flex align-items-center gap-3 px-4 py-3" style="background:#fff;border-radius:12px;border:1px solid rgba(51,46,158,.1);">
    <div>
      <div class="fw-semibold" style="font-size:0.984rem;color:#1E293B;">IP Restriction</div>
      <div style="font-size:0.816rem;color:#475569;">{{ $enabled ? 'Only listed IPs can access the system' : 'All IPs are currently allowed' }}</div>
    </div>
    <label style="cursor:pointer;" class="form-check form-switch mb-0 ps-0">
      <input type="checkbox" wire:model.live="enabled" wire:change="toggleEnabled" role="switch"
        class="form-check-input ms-0" style="width:44px;height:24px;cursor:pointer;accent-color:#332E9E;">
    </label>
  </div>
</div>

@if(session()->has('success'))
  <div class="d-flex align-items-center gap-2 mb-4 px-4 py-3" style="background:rgba(22,163,74,.08);border-radius:12px;border:1px solid rgba(22,163,74,.2);color:#15803D;font-size:0.984rem;">
    <i class="ph ph-check-circle" style="font-size:1.32rem;"></i> {{ session('success') }}
  </div>
@endif

@if(!$enabled)
  <div class="d-flex align-items-center gap-2 mb-4 px-4 py-3" style="background:rgba(245,158,11,.08);border-radius:12px;border:1px solid rgba(245,158,11,.2);color:#B45309;font-size:0.96rem;">
    <i class="ph ph-warning" style="font-size:1.32rem;"></i>
    <span><strong>Restriction is off.</strong> All IP addresses can access the system. Enable the toggle above to enforce the whitelist.</span>
  </div>
@endif

<div class="row g-4">
  <div class="col-lg-8">

    {{-- Add new IP --}}
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:22px;margin-bottom:20px;">
      <h2 style="font-size:1.056rem;font-weight:700;color:#0F172A;margin-bottom:14px;">Add IP / Network</h2>
      <div class="d-flex gap-3 flex-wrap align-items-end">
        <div style="flex:1;min-width:160px;">
          <label style="display:block;font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;margin-bottom:4px;">IP or CIDR Range</label>
          <input type="text" wire:model="newIp" class="ip-inp w-100"
            placeholder="e.g. 203.0.113.50 or 192.168.1.0/24"
            wire:keydown.enter="addIp">
          @error('newIp')<div style="font-size:0.84rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>@enderror
        </div>
        <div style="flex:1;min-width:130px;">
          <label style="display:block;font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;margin-bottom:4px;">Label (optional)</label>
          <input type="text" wire:model="newLabel" class="ip-inp w-100"
            placeholder="e.g. Office, Home, VPN"
            wire:keydown.enter="addIp">
        </div>
        <button type="button" wire:click="addIp"
          style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:10px;padding:10px 22px;font-size:0.984rem;font-weight:700;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;">
          <i class="ph ph-plus-circle"></i> Add IP
        </button>
      </div>
    </div>

    {{-- IP list --}}
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);overflow:hidden;">
      <div style="padding:16px 22px;border-bottom:1px solid rgba(51,46,158,.06);display:flex;align-items:center;justify-content:between;">
        <h2 style="font-size:1.056rem;font-weight:700;color:#0F172A;margin:0;flex:1;">Allowed IPs</h2>
        <span style="font-size:0.84rem;color:#475569;">{{ count($ips) }} {{ count($ips) === 1 ? 'entry' : 'entries' }}</span>
      </div>
      <div style="padding:16px 22px;">
        @if(empty($ips))
          <div class="text-center py-4" style="color:#475569;">
            <i class="ph ph-shield" style="font-size:2rem;display:block;margin-bottom:6px;opacity:.3;"></i>
            <div style="font-size:0.912rem;">No IPs added yet. Add an IP above to get started.</div>
          </div>
        @else
          @foreach($ips as $i => $entry)
            <div class="ip-row">
              <div style="width:36px;height:36px;border-radius:9px;background:rgba(51,46,158,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph ph-shield-check" style="font-size:1.2rem;color:#332E9E;"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:0.984rem;color:#1E293B;font-family:monospace;">{{ $entry['ip'] }}</div>
                <div style="font-size:0.816rem;color:#475569;">{{ $entry['label'] !== $entry['ip'] ? $entry['label'].' · ' : '' }}Added {{ $entry['added'] }}</div>
              </div>
              @if($entry['ip'] === request()->ip())
                <span style="background:rgba(22,163,74,.10);color:#15803D;padding:2px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;flex-shrink:0;">Your IP</span>
              @endif
              <button type="button" wire:click="removeIp({{ $i }})"
                wire:confirm="Remove {{ $entry['ip'] }} from the whitelist?"
                style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:8px;padding:5px 12px;font-size:0.864rem;font-weight:600;cursor:pointer;flex-shrink:0;">
                Remove
              </button>
            </div>
          @endforeach
        @endif
      </div>
    </div>

  </div>

  <div class="col-lg-4">
    {{-- Your IP --}}
    <div style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);padding:20px;margin-bottom:16px;text-align:center;">
      <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;margin-bottom:10px;">Your Current IP</div>
      <div style="font-size:1.38rem;font-weight:800;color:#332E9E;font-family:monospace;margin-bottom:6px;">{{ request()->ip() }}</div>
      <div style="font-size:0.816rem;color:#475569;">Add this IP to prevent locking yourself out</div>
      <button type="button" wire:click="$set('newIp', '{{ request()->ip() }}')"
        style="margin-top:12px;background:rgba(51,46,158,.08);border:none;color:#332E9E;border-radius:8px;padding:6px 16px;font-size:0.888rem;font-weight:600;cursor:pointer;">
        Use This IP
      </button>
    </div>

    {{-- Format guide --}}
    <div style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);padding:20px;">
      <div style="font-size:0.84rem;font-weight:700;color:#374151;margin-bottom:12px;">Format Guide</div>
      @foreach([['Single IP','203.0.113.50','Exact IP'],['CIDR range','192.168.1.0/24','256 addresses'],['Class A','10.0.0.0/8','All 10.x.x.x']] as [$type,$ex,$desc])
        <div class="d-flex align-items-center justify-content-between mb-2" style="font-size:0.864rem;">
          <span style="color:#475569;">{{ $type }}</span>
          <span style="font-family:monospace;color:#332E9E;background:rgba(51,46,158,.07);padding:1px 7px;border-radius:5px;">{{ $ex }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>
</div>
