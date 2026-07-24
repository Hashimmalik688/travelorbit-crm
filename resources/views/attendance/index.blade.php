@extends('layouts/contentNavbarLayout')
@section('title', 'Attendance')

@section('content')
@php
    use App\Models\Attendance;
    $canEdit = auth()->user()?->hasPermission('attendance.edit');
    $initials = function ($name) {
        return collect(explode(' ', trim((string) $name)))
            ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    };
    // Stable-ish avatar tint per name
    $avatarColors = ['#332E9E', '#0EA5E9', '#16A34A', '#D97706', '#DC2626', '#7C3AED', '#0891B2', '#DB2777'];
@endphp

<style>
    [x-cloak] { display: none !important; }
    .to-att .att-avatar {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78rem; font-weight: 700; color: #fff; letter-spacing: .02em;
    }
    .to-att .att-stat {
        border: 1px solid rgba(51,46,158,.08); border-radius: 16px; overflow: hidden;
        background: #fff; transition: box-shadow .2s ease, transform .2s ease;
    }
    .to-att .att-stat:hover { box-shadow: 0 10px 28px rgba(51,46,158,.10); transform: translateY(-2px); }
    .to-att table td { vertical-align: middle; }
    .to-att .att-edit-btn {
        border: 1px solid rgba(51,46,158,.14); background: #fff; color: #332E9E;
        width: 32px; height: 32px; border-radius: 9px; display: inline-flex;
        align-items: center; justify-content: center; cursor: pointer; transition: all .15s;
    }
    .to-att .att-edit-btn:hover { background: #332E9E; color: #fff; border-color: #332E9E; }
    .to-att .att-chip {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px;
        border-radius: 999px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
        border: 1px dashed rgba(220,38,38,.35); background: #FEF2F2; color: #b91c1c; transition: all .15s;
    }
    .to-att .att-chip:hover { background: #DC2626; color: #fff; border-color: #DC2626; }
    /* Modal */
    .att-overlay {
        position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center;
        background: rgba(15,23,42,.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); padding: 16px;
    }
    .att-modal {
        background: #fff; border-radius: 20px; width: 100%; max-width: 460px;
        box-shadow: 0 32px 96px rgba(0,0,0,.35); overflow: hidden;
        display: flex; flex-direction: column; max-height: 94vh;
    }
    .att-modal-head {
        padding: 18px 22px; background: linear-gradient(135deg,#332E9E,#4A45B5); color: #fff;
        display: flex; align-items: center; justify-content: space-between;
    }
    .att-modal-body { padding: 20px 22px; overflow-y: auto; }
    .att-label { font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 5px; display: block; }
    .att-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid rgba(51,46,158,.14);
        border-radius: 10px; font-size: 0.95rem; color: #0f172a; background: #fff;
    }
    .att-input:focus { outline: none; border-color: #332E9E; box-shadow: 0 0 0 3px rgba(51,46,158,.12); }
    .att-btn { border: none; border-radius: 10px; padding: 9px 20px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all .15s; }
    .att-btn-primary { background: linear-gradient(135deg,#332E9E,#4A45B5); color: #fff; box-shadow: 0 4px 16px rgba(51,46,158,.3); }
    .att-btn-ghost { background: transparent; border: 1.5px solid rgba(51,46,158,.18); color: #475569; }
    .att-btn-danger { background: transparent; border: none; color: #dc2626; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; padding: 0; }
    .att-btn-danger:hover { text-decoration: underline; }
</style>

<div class="to-att" x-data="attendanceEditor()">

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
            <i class="ph ph-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <i class="ph ph-warning-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ══ Header ══ --}}
    <div class="to-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="mt-1 mb-0">Attendance</h1>
            <p class="mb-0" style="font-size:0.95rem;color:#64748b;">Company-wide roster for the selected day.</p>
        </div>
        <div class="d-flex gap-2">
            @if ($canEdit)
                <button type="button" class="btn btn-primary" @click="addNew()">
                    <i class="ph ph-plus-circle me-1"></i> Add record
                </button>
            @endif
            <a href="{{ route('attendance.history') }}" class="btn btn-outline-primary">
                <i class="ph ph-clock-counter-clockwise me-1"></i> History
            </a>
        </div>
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
                <div class="att-stat h-100">
                    <div class="d-flex align-items-center gap-3 p-3">
                        <span class="d-flex align-items-center justify-content-center rounded-3"
                              style="width:46px;height:46px;background:{{ $c['color'] }}14;">
                            <i class="ph {{ $c['icon'] }}" style="font-size:1.45rem;color:{{ $c['color'] }};"></i>
                        </span>
                        <div>
                            <div style="font-size:0.76rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">{{ $c['label'] }}</div>
                            <div class="fw-bold" style="font-size:1.6rem;line-height:1.1;color:#0f172a;">{{ $c['val'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══ Filters ══ --}}
    <div class="card mb-4" style="border-radius:14px;">
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
    <div class="card" style="border-radius:14px;overflow:hidden;">
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
                        @if ($canEdit)<th class="text-end">Fix</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceDetails as $att)
                        @php
                            $swc = $att->status_with_color;
                            $name = $att->user->name ?? '—';
                            $tint = $avatarColors[($att->user_id ?? 0) % count($avatarColors)];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="att-avatar" style="background:{{ $tint }};">{{ $initials($name) }}</span>
                                    <span class="fw-semibold">{{ $name }}</span>
                                </div>
                            </td>
                            <td>{{ $att->date->format('D, M j') }}</td>
                            <td>{{ $att->formatted_login_time }}</td>
                            <td>{{ $att->formatted_logout_time }}</td>
                            <td>{{ $att->working_hours ? $att->working_hours . 'h' : '—' }}</td>
                            <td><span class="badge {{ $swc['badge'] }}">{{ $swc['label'] }}</span></td>
                            @if ($canEdit)
                                @php
                                    $rowPayload = [
                                        'id' => $att->id,
                                        'user_id' => $att->user_id,
                                        'userName' => $name,
                                        'date' => $att->date->format('Y-m-d'),
                                        'status' => $att->status,
                                        'login_time' => optional($att->login_time)->format('H:i'),
                                        'logout_time' => optional($att->logout_time)->format('H:i'),
                                    ];
                                @endphp
                                <td class="text-end">
                                    <button type="button" class="att-edit-btn" title="Fix this record"
                                        @click="editRow(@js($rowPayload))">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canEdit ? 7 : 6 }}" class="text-center py-4" style="color:#94a3b8;">No attendance records for this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ Absentees for the selected day ══ --}}
    @if ($absentEmployees->isNotEmpty())
        <div class="card mt-4" style="border-radius:14px;">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0" style="font-size:1.02rem;">Not checked in — {{ \Carbon\Carbon::parse($startDate)->format('D, M j') }}</h6>
                <span class="badge bg-label-danger">{{ $absentEmployees->count() }}</span>
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                @foreach ($absentEmployees as $emp)
                    @if ($canEdit)
                        @php $absPayload = ['user_id' => $emp->id, 'userName' => $emp->name, 'date' => $startDate]; @endphp
                        <span class="att-chip" title="Mark attendance for {{ $emp->name }}"
                            @click="addFor(@js($absPayload))">
                            <i class="ph ph-plus-circle"></i> {{ $emp->name }}
                        </span>
                    @else
                        <span class="badge bg-label-danger" style="font-size:0.78rem;">{{ $emp->name }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══ Edit / Add modal ══ --}}
    @if ($canEdit)
        <div class="att-overlay" x-show="open" x-cloak @click="open = false" @keydown.escape.window="open = false">
            <div class="att-modal" @click.stop>
                <div class="att-modal-head">
                    <h5 class="fw-bold mb-0" style="font-size:1.08rem;display:flex;align-items:center;gap:9px;">
                        <i class="ph ph-user-focus" style="font-size:1.25rem;"></i>
                        <span x-text="form.id ? 'Fix attendance' : 'Add attendance'"></span>
                    </h5>
                    <button type="button" @click="open = false"
                        style="background:rgba(255,255,255,.18);color:#fff;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;">✕</button>
                </div>

                <form method="POST" action="{{ route('attendance.save') }}" class="att-modal-body">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="id" x-model="form.id">

                    {{-- Staff --}}
                    <div class="mb-3">
                        <label class="att-label">Staff member</label>
                        <template x-if="lockUser">
                            <div>
                                <div class="att-input d-flex align-items-center" style="background:#f8fafc;font-weight:600;" x-text="form.userName"></div>
                                <input type="hidden" name="user_id" x-model="form.user_id">
                            </div>
                        </template>
                        <template x-if="!lockUser">
                            <select name="user_id" class="att-input" x-model="form.user_id" required>
                                <option value="">Select staff…</option>
                                @foreach ($allEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </template>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="att-label">Date</label>
                            <input type="date" name="date" class="att-input" x-model="form.date" required>
                        </div>
                        <div class="col-5">
                            <label class="att-label">Status</label>
                            <select name="status" class="att-input" x-model="form.status" required>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                                <option value="leave">Leave</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-1">
                        <div class="col-6">
                            <label class="att-label">Check in</label>
                            <input type="time" name="login_time" class="att-input" x-model="form.login_time">
                        </div>
                        <div class="col-6">
                            <label class="att-label">Check out</label>
                            <input type="time" name="logout_time" class="att-input" x-model="form.logout_time">
                        </div>
                    </div>
                    <p style="font-size:0.76rem;color:#94a3b8;margin:8px 2px 0;">Leave times blank for Absent / Leave. Hours are recalculated automatically.</p>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid rgba(51,46,158,.08);">
                        <button type="button" class="att-btn att-btn-ghost" @click="open = false">Cancel</button>
                        <button type="submit" class="att-btn att-btn-primary">Save</button>
                    </div>
                </form>

                {{-- Delete (separate form — editing an existing record only) --}}
                <template x-if="form.id">
                    <form method="POST" :action="deleteUrl + '/' + form.id"
                          onsubmit="return confirm('Delete this attendance record permanently?');"
                          style="padding:0 22px 18px;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                        <button type="submit" class="att-btn-danger"><i class="ph ph-trash"></i> Delete this record</button>
                    </form>
                </template>
            </div>
        </div>
    @endif
</div>

@if ($canEdit)
<script>
    window.attendanceEditor = function () {
        return {
            open: false,
            lockUser: false,
            deleteUrl: @json(url('attendance')),
            form: { id: '', user_id: '', userName: '', date: '', status: 'present', login_time: '', logout_time: '' },

            editRow(p) {
                this.form = {
                    id: p.id, user_id: p.user_id, userName: p.userName, date: p.date,
                    status: p.status, login_time: p.login_time || '', logout_time: p.logout_time || '',
                };
                this.lockUser = true;
                this.open = true;
            },
            addFor(p) {
                this.form = {
                    id: '', user_id: p.user_id, userName: p.userName, date: p.date,
                    status: 'present', login_time: '', logout_time: '',
                };
                this.lockUser = true;
                this.open = true;
            },
            addNew() {
                this.form = {
                    id: '', user_id: '', userName: '', date: @json($startDate),
                    status: 'present', login_time: '', logout_time: '',
                };
                this.lockUser = false;
                this.open = true;
            },
        };
    };
</script>
@endif
@endsection
