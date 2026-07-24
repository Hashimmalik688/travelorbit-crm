@extends('layouts/contentNavbarLayout')
@section('title', 'Attendance')

@section('content')
@php use App\Models\Attendance; @endphp

<div class="to-attendance-admin">
<div class="to-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mt-1 mb-0">Attendance</h1>
        <p class="mb-0" style="font-size:0.95rem;color:#334155;">Company-wide roster for the selected day.</p>
    </div>
    <a href="{{ route('attendance.history') }}" class="btn btn-outline-primary">
        <i class="ph ph-clock-counter-clockwise me-1"></i> History
    </a>
</div>

{{-- ══ Counts ══ --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'Staff',   'val' => $totalEmployees, 'icon' => 'ph-users',        'color' => '#332E9E'],
            ['label' => 'Present', 'val' => $presentCount,   'icon' => 'ph-check-circle', 'color' => '#16a34a'],
            ['label' => 'Late',    'val' => $lateCount,      'icon' => 'ph-timer',        'color' => '#d97706'],
            ['label' => 'Absent',  'val' => $absentCount,    'icon' => 'ph-x-circle',     'color' => '#dc2626'],
        ];
    @endphp
    @foreach ($cards as $c)
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="d-flex align-items-center justify-content-center rounded"
                          style="width:42px;height:42px;background:{{ $c['color'] }}14;">
                        <i class="ph {{ $c['icon'] }}" style="font-size:1.35rem;color:{{ $c['color'] }};"></i>
                    </span>
                    <div>
                        <div style="font-size:0.8rem;color:#64748b;text-transform:uppercase;letter-spacing:.03em;">{{ $c['label'] }}</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1.1;">{{ $c['val'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ══ Filters ══ --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">Name</label>
                <input type="text" name="search_name" value="{{ $searchName }}" placeholder="Search staff…" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:0.8rem;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ([Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_ABSENT, Attendance::STATUS_LEAVE] as $s)
                        <option value="{{ $s }}" @selected($searchStatus === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100"><i class="ph ph-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Roster ══ --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="font-size:1.02rem;">Records</h6>
        <span class="badge bg-label-primary">{{ $attendanceDetails->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendanceDetails as $att)
                    @php $swc = $att->status_with_color; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $att->user->name ?? '—' }}</td>
                        <td>{{ $att->date->format('D, M j') }}</td>
                        <td>{{ $att->formatted_login_time }}</td>
                        <td>{{ $att->formatted_logout_time }}</td>
                        <td>{{ $att->working_hours ? $att->working_hours . 'h' : '—' }}</td>
                        <td><span class="badge {{ $swc['badge'] }}">{{ $swc['label'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">No attendance records for this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══ Absentees for the selected day ══ --}}
@if ($absentEmployees->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="fw-bold mb-0" style="font-size:1.02rem;">Not checked in — {{ \Carbon\Carbon::parse($startDate)->format('D, M j') }}</h6>
        </div>
        <div class="card-body d-flex flex-wrap gap-2">
            @foreach ($absentEmployees as $emp)
                <span class="badge bg-label-danger" style="font-size:0.78rem;">{{ $emp->name }}</span>
            @endforeach
        </div>
    </div>
@endif
</div>
@endsection
