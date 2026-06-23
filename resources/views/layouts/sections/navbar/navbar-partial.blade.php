@php
    use Illuminate\Support\Facades\Auth;
    $isMobile = false; // set dynamically in controller if needed
@endphp

{{-- Mobile toggle only --}}
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-2 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0" href="javascript:void(0)" onclick="document.documentElement.classList.toggle('layout-menu-collapsed')">
        <i class="ph ph-list icon-md"></i>
    </a>
</div>
@endif

{{-- Floating nav container - full-width with content inside --}}
<div class="to-navbar w-100 d-flex align-items-center justify-content-between">

    {{-- Left: Search --}}
    <div class="to-navbar-search">
        @livewire('global-search')
    </div>

    {{-- Right: Utility icons --}}
    <div class="to-navbar-actions d-flex align-items-center gap-2">
        {{-- Notification bell --}}
        @livewire('notification-bell')


    </div>
</div>
