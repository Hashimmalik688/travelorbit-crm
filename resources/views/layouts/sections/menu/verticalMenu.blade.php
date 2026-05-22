@php
use Illuminate\Support\Facades\Route;
$user        = Auth::user();
$userRole    = $user ? $user->role : 'agent';
$isAdmin     = ($userRole === 'admin');
$currentRoute = Route::currentRouteName();
$bottomSlugs  = ['settings', 'settings.users', 'settings.audit-log', 'settings.profile'];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  {{-- ── Brand header ── --}}
  <div class="sb-header">
    <div class="sb-brand">
      <img src="{{ asset('logo.png') }}" alt="Travel Orbit" class="sb-brand-logo">
    </div>
    <a href="javascript:void(0);" class="sb-toggle" onclick="var h=document.documentElement;var i=this.querySelector('i');h.classList.toggle('layout-menu-collapsed');i.className=h.classList.contains('layout-menu-collapsed')?'ph ph-caret-right sb-toggle-icon':'ph ph-caret-left sb-toggle-icon'" aria-label="Toggle sidebar">
      <i class="ph ph-caret-left sb-toggle-icon"></i>
    </a>
  </div>

  {{-- ── Main navigation ── --}}
  <ul class="menu-inner sb-nav">
    @foreach ($menuData[0]->menu as $menu)
      @php $hasRole = $isAdmin || !isset($menu->roles) || in_array($userRole, $menu->roles); @endphp
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
           @if (!empty($menu->target)) target="_blank" @endif>
          @isset($menu->icon)
            <i class="{{ $menu->icon }}"></i>
          @endisset
          <div class="sb-link-text">{{ $menu->name ?? '' }}</div>
        </a>
        @isset($menu->submenu)
          @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
        @endisset
      </li>
    @endforeach
  </ul>

  {{-- ── Bottom pinned ── --}}
  <div class="sb-bottom">
    <ul class="sb-bottom-nav">
      @if ($isAdmin)
        <li class="menu-item {{ str_starts_with($currentRoute, 'settings') && $currentRoute !== 'settings.profile' ? 'active' : '' }}">
          <a href="{{ route('settings') }}" class="menu-link">
            <i class="menu-icon icon-base bx bx-cog"></i>
            <div class="sb-link-text">Settings</div>
          </a>
        </li>
      @endif
      <li class="menu-item {{ $currentRoute === 'settings.profile' ? 'active' : '' }}">
        <a href="{{ route('settings.profile') }}" class="menu-link">
          <i class="menu-icon icon-base bx bx-user-circle"></i>
          <div class="sb-link-text">Profile</div>
        </a>
      </li>
    </ul>

    {{-- User card with logout --}}
    <div class="sb-user-card">
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
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sb-logout" title="Logout">
          <i class="bx bx-log-out"></i>
        </button>
      </form>
    </div>
  </div>

</aside>
