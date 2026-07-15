@extends('layouts/contentNavbarLayout')
@section('title', 'Settings')
@section('content')

<div class="mb-5">
  <div style="font-size:0.864rem;color:#475569;margin-bottom:4px;"><a href="{{ route('dashboard') }}" style="color:#475569;text-decoration:none;">Dashboard</a> › Settings</div>
  <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;margin:0;">Settings</h1>
</div>

<div class="row g-3">
  @php
    $cards = [
      [
        'title' => 'User Management',
        'desc'  => 'Add, edit and manage system users. Assign roles: Agent, Accounts, Issuance, Manager, Admin.',
        'icon'  => 'ph ph-users-three',
        'color' => '#16A34A',
        'bg'    => 'rgba(22,163,74,.08)',
        'url'   => route('settings.users'),
        'meta'  => null,
      ],
      [
        'title' => 'Activity Log',
        'desc'  => 'Full audit trail - every login, booking change, payment action and status update.',
        'icon'  => 'ph ph-clock-counter-clockwise',
        'color' => '#0EA5E9',
        'bg'    => 'rgba(14,165,233,.08)',
        'url'   => route('settings.activity'),
        'meta'  => null,
      ],
      [
        'title' => 'IP Whitelist',
        'desc'  => 'Restrict system access to specific IP addresses or CIDR network ranges.',
        'icon'  => 'ph ph-shield-check',
        'color' => '#D97706',
        'bg'    => 'rgba(217,119,6,.08)',
        'url'   => route('settings.ip'),
        'meta'  => 'Security',
      ],
      [
        'title' => 'Flight Vendors',
        'desc'  => 'Add, remove or edit vendor options shown in the booking flight form.',
        'icon'  => 'ph ph-airplane-tilt',
        'color' => '#FF6B35',
        'bg'    => 'rgba(255,107,53,.08)',
        'url'   => route('settings.vendors'),
        'meta'  => 'Booking',
      ],
      [
        'title' => 'GDS Options',
        'desc'  => 'Add, remove or edit GDS options (Amadeus, Sabre, etc.) shown in the booking flight form.',
        'icon'  => 'ph ph-globe',
        'color' => '#7C3AED',
        'bg'    => 'rgba(124,58,237,.08)',
        'url'   => route('settings.gds'),
        'meta'  => 'Booking',
      ],
    ];
  @endphp
  @foreach($cards as $c)
    <div class="col-md-4">
      <a href="{{ $c['url'] }}" style="display:block;text-decoration:none;background:#fff;border-radius:18px;border:1px solid rgba(51,46,158,.08);padding:28px 26px;box-shadow:0 2px 12px rgba(51,46,158,.04);transition:all .18s;position:relative;overflow:hidden;"
        onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 30px rgba(51,46,158,.12)';this.style.borderColor='rgba(51,46,158,.18)'"
        onmouseleave="this.style.transform='';this.style.boxShadow='0 2px 12px rgba(51,46,158,.04)';this.style.borderColor='rgba(51,46,158,.08)'">
        {{-- Background glow --}}
        <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;border-radius:50%;background:{{ $c['bg'] }};opacity:.4;pointer-events:none;"></div>
        <div style="width:48px;height:48px;border-radius:14px;background:{{ $c['bg'] }};display:flex;align-items:center;justify-content:center;margin-bottom:18px;position:relative;">
          <i class="{{ $c['icon'] }}" style="font-size:1.56rem;color:{{ $c['color'] }};"></i>
        </div>
        <div class="fw-bold" style="font-size:1.14rem;color:#0F172A;margin-bottom:6px;">{{ $c['title'] }}</div>
        <div style="font-size:0.888rem;color:#475569;line-height:1.55;margin-bottom:14px;">{{ $c['desc'] }}</div>
        <div style="font-size:0.9rem;font-weight:700;color:{{ $c['color'] }};display:inline-flex;align-items:center;gap:4px;">
          @if($c['meta'])
            <span style="background:{{ $c['bg'] }};padding:1px 8px;border-radius:20px;font-size:0.78rem;">{{ $c['meta'] }}</span>
            &nbsp;·&nbsp;
          @endif
          Open <i class="ph ph-arrow-right" style="font-size:0.9rem;"></i>
        </div>
      </a>
    </div>
  @endforeach
</div>

{{-- System info strip --}}
<div class="d-flex align-items-center gap-4 mt-5 px-4 py-3" style="background:#fff;border-radius:12px;border:1px solid rgba(51,46,158,.07);">
  <span style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;">System</span>
  @foreach(['PHP'=>phpversion(),'Laravel'=>app()->version(),'Environment'=>ucfirst(app()->environment())] as $k=>$v)
    <div style="font-size:0.864rem;color:#374151;"><span style="color:#475569;">{{ $k }}</span> &nbsp;<span class="fw-semibold" style="font-family:monospace;">{{ $v }}</span></div>
  @endforeach
</div>
@endsection
