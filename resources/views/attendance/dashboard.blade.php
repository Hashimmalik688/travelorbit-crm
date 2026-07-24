@extends('layouts/contentNavbarLayout')
@section('title', 'My Attendance')

@section('content')
@php
    use App\Models\Attendance;
    $prevMonth = \Carbon\Carbon::parse($currentMonth . '-01')->subMonth()->format('Y-m');
    $nextMonth = \Carbon\Carbon::parse($currentMonth . '-01')->addMonth()->format('Y-m');
@endphp

<div class="to-attendance">
<div class="to-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mt-1 mb-0">My Attendance</h1>
        <p class="mb-0" style="font-size:0.95rem;color:#334155;">Check in when you start work, check out when you finish.</p>
    </div>
</div>

{{-- ══ Today's status + check-in / check-out ══ --}}
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:52px;height:52px;background:rgba(51,46,158,.08);">
                <i class="ph ph-clock-user" style="font-size:1.6rem;color:#332E9E;"></i>
            </span>
            <div>
                <div class="fw-bold" style="font-size:1.05rem;">{{ now()->format('l, F j, Y') }}</div>
                <div id="today-status" style="font-size:0.9rem;color:#475569;">
                    @if ($todayAttendance)
                        Checked in at <strong>{{ $todayAttendance->formatted_login_time }}</strong>
                        @if ($todayAttendance->logout_time)
                            · Checked out at <strong>{{ $todayAttendance->formatted_logout_time }}</strong>
                        @endif
                        @php $swc = $todayAttendance->status_with_color; @endphp
                        <span class="badge {{ $swc['badge'] }} ms-1">{{ $swc['label'] }}</span>
                    @else
                        You haven't checked in today.
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button id="btn-checkin" class="btn btn-primary" @if($todayAttendance) disabled @endif>
                <i class="ph ph-sign-in me-1"></i> Check In
            </button>
            <button id="btn-checkout" class="btn btn-outline-primary" @if(!$canCheckout) disabled @endif>
                <i class="ph ph-sign-out me-1"></i> Check Out
            </button>
        </div>
    </div>
</div>

{{-- ══ Month stats ══ --}}
<div class="row g-3 mb-4">
    @php
        $tiles = [
            ['label' => 'Present',       'val' => $stats['present'],     'icon' => 'ph-check-circle', 'color' => '#16a34a'],
            ['label' => 'Late',          'val' => $stats['late'],        'icon' => 'ph-timer',        'color' => '#d97706'],
            ['label' => 'Absent',        'val' => $stats['absent'],      'icon' => 'ph-x-circle',     'color' => '#dc2626'],
            ['label' => 'Working Days',  'val' => $stats['total_days'],  'icon' => 'ph-calendar-dots','color' => '#332E9E'],
            ['label' => 'Total Hours',   'val' => $stats['total_hours'], 'icon' => 'ph-hourglass',    'color' => '#0891b2'],
        ];
    @endphp
    @foreach ($tiles as $t)
        <div class="col-6 col-lg">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="d-flex align-items-center justify-content-center rounded"
                          style="width:42px;height:42px;background:{{ $t['color'] }}14;">
                        <i class="ph {{ $t['icon'] }}" style="font-size:1.35rem;color:{{ $t['color'] }};"></i>
                    </span>
                    <div>
                        <div style="font-size:0.8rem;color:#64748b;text-transform:uppercase;letter-spacing:.03em;">{{ $t['label'] }}</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1.1;">{{ $t['val'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ══ Calendar ══ --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="font-size:1.02rem;">{{ $monthLabel }}</h6>
        <div class="btn-group">
            <a href="{{ route('attendance.dashboard', ['month' => $prevMonth]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ph ph-caret-left"></i>
            </a>
            <a href="{{ route('attendance.dashboard') }}" class="btn btn-sm btn-outline-secondary">Today</a>
            <a href="{{ route('attendance.dashboard', ['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ph ph-caret-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered mb-0 to-cal">
            <thead>
                <tr>
                    @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)
                        <th class="text-center" style="font-size:0.78rem;color:#64748b;">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($calendar as $week)
                    <tr>
                        @foreach ($week as $day)
                            @php
                                $att = $day['attendance'];
                                $isWeekend = in_array($day['date']->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
                                $swc = $att ? $att->status_with_color : null;
                            @endphp
                            <td class="align-top {{ !$day['isCurrentMonth'] ? 'to-cal-off' : '' }} {{ $day['isToday'] ? 'to-cal-today' : '' }}"
                                style="height:84px;width:14.28%;{{ $isWeekend && $day['isCurrentMonth'] ? 'background:#f8fafc;' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span style="font-size:0.85rem;font-weight:600;color:{{ $day['isCurrentMonth'] ? '#1e293b' : '#cbd5e1' }};">
                                        {{ $day['date']->format('j') }}
                                    </span>
                                    @if ($day['isToday'])
                                        <span class="badge bg-primary" style="font-size:0.6rem;">Today</span>
                                    @endif
                                </div>
                                @if ($day['holiday'])
                                    <div class="mt-1">
                                        <span class="badge bg-label-info" style="font-size:0.62rem;">Holiday</span>
                                        <div style="font-size:0.66rem;color:#94a3b8;margin-top:2px;">{{ $day['holiday'] }}</div>
                                    </div>
                                @elseif ($att)
                                    <div class="mt-1">
                                        <span class="badge {{ $swc['badge'] }}" style="font-size:0.62rem;">{{ $swc['label'] }}</span>
                                        <div style="font-size:0.68rem;color:#64748b;margin-top:2px;">
                                            {{ $att->formatted_login_time }}
                                            @if ($att->logout_time) – {{ $att->formatted_logout_time }} @endif
                                        </div>
                                        @if ($att->working_hours)
                                            <div style="font-size:0.66rem;color:#94a3b8;">{{ $att->working_hours }}h</div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

<style>
.to-attendance .to-cal td.to-cal-off { background:#fcfcfd; }
.to-attendance .to-cal td.to-cal-today { box-shadow: inset 0 0 0 2px #332E9E; }
.to-attendance .to-cal th, .to-attendance .to-cal td { padding:.4rem .5rem; }
</style>
@endsection

@section('page-script')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const statusEl = document.getElementById('today-status');
    const btnIn  = document.getElementById('btn-checkin');
    const btnOut = document.getElementById('btn-checkout');

    async function post(url) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        return res.json();
    }

    function flash(msg, ok) {
        statusEl.innerHTML = '<span class="' + (ok ? 'text-success' : 'text-danger') + '">' + msg + '</span>';
    }

    btnIn?.addEventListener('click', async () => {
        btnIn.disabled = true;
        try {
            const r = await post(@json(route('attendance.checkin')));
            flash(r.message, r.success);
            if (r.success) { setTimeout(() => location.reload(), 800); }
            else { btnIn.disabled = false; }
        } catch (e) { flash('Something went wrong. Please try again.', false); btnIn.disabled = false; }
    });

    btnOut?.addEventListener('click', async () => {
        btnOut.disabled = true;
        try {
            const r = await post(@json(route('attendance.checkout')));
            flash(r.message, r.success);
            if (r.success) { setTimeout(() => location.reload(), 800); }
            else { btnOut.disabled = false; }
        } catch (e) { flash('Something went wrong. Please try again.', false); btnOut.disabled = false; }
    });
})();
</script>
@endsection
