@php
use Illuminate\Support\Facades\Route;
$user        = Auth::user();
$userRole    = $user ? $user->role : 'agent';
$isAdmin     = ($userRole === 'admin');
$currentRoute = Route::currentRouteName();
$bottomSlugs  = [];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  {{-- ── Brand header ── --}}
  <div class="sb-header">
    <div class="sb-brand">
      <span class="sb-brand-text" data-collapsed="TO">Travel Orbit</span>
    </div>
    <a href="javascript:void(0);" class="sb-toggle" onclick="var h=document.documentElement;var i=this.querySelector('i');h.classList.toggle('layout-menu-collapsed');i.className=h.classList.contains('layout-menu-collapsed')?'ph ph-caret-right sb-toggle-icon':'ph ph-caret-left sb-toggle-icon'" aria-label="Toggle sidebar">
      <i class="ph ph-caret-left sb-toggle-icon"></i>
    </a>
  </div>

  {{-- ── Main navigation ── --}}
  <ul class="menu-inner sb-nav">
    @foreach ($menuData[0]->menu as $menu)
      @php
        $menuRoles = isset($menu->roles) ? (array)$menu->roles : null;
        $hasRole = !$menuRoles || in_array($userRole, $menuRoles);
      @endphp
      @continue(!$hasRole)
      @continue(isset($menu->menuHeader))
      @continue(isset($menu->slug) && in_array($menu->slug, $bottomSlugs))

      @php
        $activeClass = null;
        if ($currentRoute === $menu->slug) {
          $activeClass = 'active';
        } elseif (isset($menu->submenu)) {
          $slug = $menu->slug;
          if (gettype($slug) === 'array') {
            foreach ($slug as $s) {
              if (str_starts_with($currentRoute, $s)) { $activeClass = 'active open'; break; }
            }
          } elseif (str_starts_with($currentRoute, $slug)) {
            $activeClass = 'active open';
          }
        }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
           class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
           title="{{ $menu->name ?? '' }}"
           @if (!empty($menu->target)) target="_blank" @endif>
          @isset($menu->icon)
            <span class="menu-icon"><i class="{{ $menu->icon }}"></i></span>
          @endisset
          <div class="sb-link-text">{{ $menu->name ?? '' }}</div>
        </a>
        @isset($menu->submenu)
          @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
        @endisset
      </li>
    @endforeach
  </ul>

  {{-- User card with dropdown --}}
  <div x-data="{ open: false }" x-on:click.outside="open = false" style="position:relative;margin:0 8px 8px;flex-shrink:0;">

    {{-- Trigger --}}
    <button type="button" @click="open = !open"
      class="sb-user-card"
      style="width:100%;border:none;cursor:pointer;background:rgba(255,255,255,.04);text-align:left;">
      <div class="sb-uc-avatar">
        @if ($user && $user->profile_photo_path)
          <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}">
        @else
          <span class="sb-uc-initials">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
        @endif
      </div>
      <div class="sb-uc-info">
        <span class="sb-uc-name">{{ $user->name ?? 'User' }}</span>
        <span class="sb-uc-role">{{ ucfirst($user->role ?? 'Agent') }}</span>
      </div>
      <i class="ph ph-caret-up-down" style="color:rgba(255,255,255,.25);font-size:.75rem;flex-shrink:0;margin-left:auto;"></i>
    </button>

    {{-- Dropdown - Glassmorphism --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
      x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
      style="position:absolute;bottom:calc(100% + 6px);left:0;right:0;background:linear-gradient(135deg,rgba(26,31,53,0.95) 0%,rgba(15,23,42,0.98) 100%);border:1px solid rgba(79,70,229,0.2);border-radius:14px;overflow:hidden;box-shadow:0 -8px 32px rgba(0,0,0,0.4),0 0 20px rgba(79,70,229,0.1);z-index:999;backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
      @if($isAdmin)
      <a href="{{ route('settings') }}"
        style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.75);text-decoration:none;font-size:.82rem;font-weight:500;transition:all .12s;background:transparent;"
        onmouseenter="this.style.background='rgba(79,70,229,0.15)'" onmouseleave="this.style.background='transparent'">
        <i class="ph ph-gear" style="font-size:1rem;color:#A5B4FC;"></i> Settings
      </a>
      <div style="height:1px;background:rgba(79,70,229,0.15);margin:4px 8px;"></div>
      @endif
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
          style="width:100%;display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(244,63,94,0.85);background:none;border:none;font-size:.82rem;font-weight:600;cursor:pointer;text-align:left;transition:all .12s;"
          onmouseenter="this.style.background='rgba(244,63,94,0.12)'" onmouseleave="this.style.background='transparent'">
          <i class="ph ph-sign-out" style="font-size:1rem;"></i> Sign Out
        </button>
      </form>
    </div>
  </div>

</aside>
