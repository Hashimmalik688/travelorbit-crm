@php
    use Illuminate\Support\Facades\Auth;
    $isMobile = false; // set dynamically in controller if needed

    $callDeskPendingCallbacks = \App\Models\CallCenterFollowup::where('status', 'pending')
        ->when(! Auth::user()->canViewAllData(), fn ($q) => $q->where('user_id', Auth::id()))
        ->count();
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
        {{-- Call Desk — opens the call center in a new tab. This is the only
             entry point (no sidebar item); hidden without calldesk.access so
             it can't bounce the user off the permission-gated route. --}}
        @if(auth()->user()?->hasPermission('calldesk.access'))
        <a href="{{ route('calldesk.dashboard') }}" target="_blank" rel="noopener" class="to-nav-btn" title="Call Desk">
            <i class="ph ph-headset"></i>
            @if($callDeskPendingCallbacks > 0)
                <span class="to-nav-badge" style="min-width:16px;height:16px;border-radius:8px;font-size:0.696rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;top:3px;right:3px;">
                    {{ $callDeskPendingCallbacks > 9 ? '9+' : $callDeskPendingCallbacks }}
                </span>
            @endif
        </a>
        @endif

        {{-- Notification bell --}}
        @livewire('notification-bell')


    </div>
</div>
