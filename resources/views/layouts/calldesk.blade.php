<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Call Desk</title>
    @vite(['resources/css/calldesk.css'])
    @livewireStyles
</head>
<body class="h-full min-h-screen">

@php
    use Illuminate\Support\Facades\Auth;

    $agent = Auth::user();
    $isManager = $agent->isManager();

    $pendingCallbacks = \App\Models\CallCenterFollowup::where('status', 'pending')
        ->when(! $isManager, fn ($q) => $q->where('user_id', $agent->id))
        ->count();

    $nav = [
        ['route' => 'calldesk.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['route' => 'calldesk.new-call',  'label' => 'New call',  'icon' => 'phone'],
        ['route' => 'calldesk.inquiries', 'label' => 'Inquiries', 'icon' => 'inbox'],
        ['route' => 'calldesk.callbacks', 'label' => 'Callbacks', 'icon' => 'calendar'],
    ];
@endphp

<div x-data="{ sidebarOpen: false }" class="min-h-screen">
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" style="display:none"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-line flex flex-col
                  transition-transform duration-200 lg:translate-x-0">
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-line">
            <span class="grid place-items-center w-9 h-9 rounded-lg bg-brand-600 text-white shadow-sm">
                <x-call-desk.icon name="phone" class="w-5 h-5" />
            </span>
            <div class="leading-tight">
                <div class="font-bold text-ink">Call Desk</div>
                <div class="text-[11px] text-muted">TravelOrbit CRM</div>
            </div>
        </div>

        <div class="px-5 pt-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors">
                &larr; Back to TravelOrbit CRM
            </a>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            @foreach($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="nav-item {{ request()->routeIs($item['route']) ? 'nav-item-active' : '' }}">
                    <x-call-desk.icon name="{{ $item['icon'] }}" class="w-5 h-5 shrink-0" />
                    <span>{{ $item['label'] }}</span>
                    @if($item['route'] === 'calldesk.callbacks' && $pendingCallbacks > 0)
                        <span class="ml-auto badge badge-quoted">{{ $pendingCallbacks }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Agent card + logout --}}
        <div class="p-3 border-t border-line">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                <span class="grid place-items-center w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-semibold text-sm">
                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1 leading-tight">
                    <div class="text-sm font-semibold text-ink truncate">{{ $agent->name }}</div>
                    <div class="text-[11px] text-muted">{{ $agent->roleLabel() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out"
                            class="grid place-items-center w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                        <x-call-desk.icon name="logout" class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 h-16 bg-canvas/80 backdrop-blur border-b border-line
                       flex items-center gap-3 px-4 sm:px-6">
            <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-ink p-1">
                <x-call-desk.icon name="menu" class="w-6 h-6" />
            </button>
            <h1 class="text-lg font-bold text-ink">@yield('title', 'Dashboard')</h1>
            <a href="{{ route('calldesk.new-call') }}" class="btn btn-primary btn-sm ml-auto">
                <x-call-desk.icon name="plus" class="w-4 h-4" /> New call
            </a>
        </header>

        <main class="p-4 sm:p-6 max-w-7xl mx-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast region --}}
<div x-data="toasts()" @notify.window="push($event.detail)"
     class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 w-80 max-w-[calc(100vw-2.5rem)]">
    <template x-for="t in items" :key="t.id">
        <div x-show="t.show" x-transition.opacity.duration.200ms
             class="card px-4 py-3 flex items-start gap-3"
             :class="t.type === 'error' ? 'border-rose-200' : 'border-emerald-200'">
            <span class="mt-0.5" :class="t.type === 'error' ? 'text-rose-500' : 'text-emerald-500'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <p class="text-sm text-slate-700 flex-1" x-text="t.message"></p>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toasts', () => ({
            items: [],
            push(detail) {
                const id = Date.now() + Math.random();
                this.items.push({ id, message: detail.message, type: detail.type || 'success', show: true });
                setTimeout(() => {
                    const item = this.items.find(i => i.id === id);
                    if (item) item.show = false;
                    setTimeout(() => { this.items = this.items.filter(i => i.id !== id); }, 250);
                }, 4000);
            },
        }));
    });
</script>

@livewireScripts
</body>
</html>
