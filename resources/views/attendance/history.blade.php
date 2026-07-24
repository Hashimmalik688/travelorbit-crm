@extends('layouts/contentNavbarLayout')
@section('title', 'Attendance History')

@section('content')
@php use App\Models\Attendance; @endphp

<div class="to-attendance-admin">
<div class="to-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mt-1 mb-0">Attendance History</h1>
        <p class="mb-0" style="font-size:0.95rem;color:#334155;">Filter records across any date range.</p>
    </div>
    <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">
        <i class="ph ph-calendar-check me-1"></i> Today's Roster
    </a>
</div>

{{-- ══ Summary ══ --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'Records',       'val' => $summaryStats['total_records'], 'icon' => 'ph-rows',         'color' => '#332E9E'],
            ['label' => 'Present',       'val' => $summaryStats['present'],       'icon' => 'ph-check-circle',  'color' => '#16a34a'],
            ['label' => 'Late',          'val' => $summaryStats['late'],          'icon' => 'ph-timer',         'color' => '#d97706'],
            ['label' => 'Avg Check-in',  'val' => $summaryStats['avg_login_time'],'icon' => 'ph-clock',         'color' => '#0891b2'],
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
        <form method="GET" action="{{ route('attendance.history') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:0.8rem;">Staff</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All staff</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected((string) $userId === (string) $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label" style="font-size:0.8rem;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ([Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_ABSENT, Attendance::STATUS_LEAVE] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-2 col-md-1">
                <button class="btn btn-primary btn-sm w-100"><i class="ph ph-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Table ══ --}}
<div class="card">
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
                @forelse ($attendances as $att)
                    @php $swc = $att->status_with_color; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $att->user->name ?? '—' }}</td>
                        <td>{{ $att->date->format('D, M j, Y') }}</td>
                        <td>{{ $att->formatted_login_time }}</td>
                        <td>{{ $att->formatted_logout_time }}</td>
                        <td>{{ $att->working_hours ? $att->working_hours . 'h' : '—' }}</td>
                        <td><span class="badge {{ $swc['badge'] }}">{{ $swc['label'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">No records match this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($attendances->hasPages())
        <div class="card-footer">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
</div>
@endsection
