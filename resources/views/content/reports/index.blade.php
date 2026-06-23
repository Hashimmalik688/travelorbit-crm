@extends('layouts/contentNavbarLayout')
@section('title', 'Reports Hub')
@section('content')

<div class="mb-4">
  <div style="font-size:.72rem;color:#94A3B8;margin-bottom:4px;"><a href="{{ route('dashboard') }}" style="color:#94A3B8;text-decoration:none;">Dashboard</a> › Reports</div>
  <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;margin:0;">Reports</h1>
  <div style="font-size:.75rem;color:#94A3B8;margin-top:2px;">Analytics and performance insights</div>
</div>

<div class="row g-3">
  @php
    $reports = [
      ['title'=>'Sales Report',      'desc'=>'Monthly and yearly booking revenue, margins and payment breakdown','icon'=>'ph ph-chart-bar',  'color'=>'#332E9E','bg'=>'rgba(51,46,158,.08)','url'=>route('reports.sales')],
      ['title'=>'Agent Performance', 'desc'=>'Bookings per agent, conversion rates and monthly targets',         'icon'=>'ph ph-trend-up',    'color'=>'#16A34A','bg'=>'rgba(22,163,74,.08)', 'url'=>route('reports.performance')],
    ];
  @endphp
  @foreach($reports as $r)
    <div class="col-md-4">
      <a href="{{ $r['url'] }}" style="display:block;text-decoration:none;background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:28px;box-shadow:0 2px 12px rgba(51,46,158,.04);transition:all .18s;"
        onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(51,46,158,.10)'"
        onmouseleave="this.style.transform='';this.style.boxShadow='0 2px 12px rgba(51,46,158,.04)'">
        <div style="width:48px;height:48px;border-radius:14px;background:{{ $r['bg'] }};display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <i class="{{ $r['icon'] }}" style="font-size:1.3rem;color:{{ $r['color'] }};"></i>
        </div>
        <div class="fw-bold" style="font-size:1rem;color:#0F172A;margin-bottom:6px;">{{ $r['title'] }}</div>
        <div style="font-size:.75rem;color:#94A3B8;line-height:1.5;">{{ $r['desc'] }}</div>
        <div style="margin-top:14px;font-size:.75rem;font-weight:600;color:{{ $r['color'] }};display:flex;align-items:center;gap:4px;">
          View Report <i class="ph ph-arrow-right" style="font-size:.75rem;"></i>
        </div>
      </a>
    </div>
  @endforeach
</div>
@endsection
