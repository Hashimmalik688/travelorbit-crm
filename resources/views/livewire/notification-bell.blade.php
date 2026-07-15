<div x-data="{ open: @entangle('open') }" x-on:click.outside="open = false; $wire.close()" style="position:relative;">

  {{-- Bell button --}}
  <button type="button" wire:click="toggle" class="to-nav-btn" title="Notifications">
    <i class="ph ph-bell"></i>
    @if($notifications->count() > 0)
      <span class="to-nav-badge" style="min-width:16px;height:16px;border-radius:8px;font-size:0.696rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;top:3px;right:3px;">
        {{ $notifications->count() > 9 ? '9+' : $notifications->count() }}
      </span>
    @endif
  </button>

  {{-- Dropdown --}}
  <div x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    style="position:fixed;top:60px;right:16px;width:320px;background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.10);box-shadow:0 8px 32px rgba(51,46,158,.12);z-index:9999;overflow:hidden;">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(51,46,158,.07);">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold" style="font-size:1.02rem;color:#0F172A;">Notifications</span>
        @if($notifications->count() > 0)
          <span style="background:rgba(51,46,158,.10);color:#332E9E;border-radius:20px;font-size:0.744rem;font-weight:700;padding:1px 7px;">{{ $notifications->count() }}</span>
        @endif
      </div>
      <button type="button" @click="open=false;$wire.close()" style="background:none;border:none;color:#475569;cursor:pointer;font-size:1.08rem;padding:2px;line-height:1;">✕</button>
    </div>

    {{-- Items --}}
    @if($notifications->isEmpty())
      <div class="text-center py-5" style="color:#475569;">
        <i class="ph ph-check-circle" style="font-size:2rem;display:block;margin-bottom:6px;opacity:.5;color:#16A34A;"></i>
        <div style="font-size:0.9rem;color:#475569;">All clear - no alerts right now</div>
      </div>
    @else
      <div style="max-height:320px;overflow-y:auto;">
        @foreach($notifications as $notif)
          <a href="{{ $notif['url'] }}" wire:click="close"
            style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;text-decoration:none;border-bottom:1px solid rgba(51,46,158,.05);transition:background .12s;"
            onmouseenter="this.style.background='#F8FAFF'" onmouseleave="this.style.background=''">
            <div style="width:34px;height:34px;border-radius:10px;background:{{ $notif['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ph {{ $notif['icon'] }}" style="font-size:1.14rem;color:{{ $notif['color'] }};"></i>
            </div>
            <div class="flex-grow-1">
              <div style="font-size:0.936rem;font-weight:600;color:#1E293B;line-height:1.3;">{{ $notif['title'] }}</div>
              <div style="font-size:0.816rem;color:#475569;margin-top:2px;">{{ $notif['sub'] }}</div>
            </div>
            <i class="ph ph-arrow-right" style="color:#64748B;font-size:0.936rem;margin-top:4px;flex-shrink:0;"></i>
          </a>
        @endforeach
      </div>
    @endif

    <div class="px-4 py-2" style="border-top:1px solid rgba(51,46,158,.06);background:#F8FAFF;text-align:center;">
      <span style="font-size:0.804rem;color:#64748B;">Live data · based on your role</span>
    </div>
  </div>
</div>
