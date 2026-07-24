<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Attendance — ported from taurus-crm (self-service + admin view scope).
 *
 * Taurus tracked attendance by spatie roles; here "trackable" is every user
 * except the god-mode admin (who operates untracked, see User::isGodMode).
 * Public holidays are not modelled in this app — only weekends are skipped.
 */
class AttendanceController extends Controller
{
    private $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /** Users whose attendance is tracked: everyone but the untracked admin. */
    private function getTrackableUsers()
    {
        return User::where('role', '!=', User::ROLE_ADMIN);
    }

    // ── Self-service ─────────────────────────────────────────────────

    /** AJAX: Check-in (mark attendance). */
    public function checkIn(Request $request)
    {
        $force = $request->input('force_office', false) ? true : false;
        $result = $this->attendanceService->markAttendance(auth()->id(), $force);

        if (! isset($result['success'])) {
            $result['success'] = false;
        }

        return response()->json($result);
    }

    /** AJAX: Check-out (mark logout). */
    public function checkOut(Request $request)
    {
        $result = $this->attendanceService->markLogout(auth()->id());

        return response()->json($result);
    }

    /** Personal attendance dashboard with a monthly calendar view. */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $date = Carbon::parse($currentMonth . '-01');

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Attendance records for the month, keyed by Y-m-d
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(function ($a) { return $a->date->format('Y-m-d'); });

        // Build the calendar grid (Monday-first weeks)
        $holidayMap = AttendanceService::holidayMap();
        $calendar = [];
        $cursor = $startOfMonth->copy()->startOfWeek();       // Monday
        $lastDayOfWeek = $endOfMonth->copy()->endOfWeek();    // Sunday
        $week = [];

        while ($cursor <= $lastDayOfWeek) {
            $dateKey = $cursor->format('Y-m-d');
            $week[] = [
                'date' => $cursor->copy(),
                'dateKey' => $dateKey,
                'isCurrentMonth' => $cursor->between($startOfMonth, $endOfMonth),
                'attendance' => $attendances->get($dateKey),
                'holiday' => $holidayMap[$dateKey] ?? null,
                'isToday' => $cursor->isToday(),
            ];

            if ($cursor->dayOfWeek === Carbon::SUNDAY) {
                $calendar[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }
        if (count($week) > 0) {
            $calendar[] = $week;
        }

        // Month statistics
        $now = Carbon::now();
        $officeStartTimeRaw = \App\Models\Setting::getValue('office_start_time', '09:00');
        try {
            $shiftStart = Carbon::createFromFormat('H:i', $officeStartTimeRaw);
        } catch (\Exception $e) {
            $shiftStart = Carbon::createFromFormat('h:i A', $officeStartTimeRaw);
        }

        $totalDays = $present = $late = $absent = 0;
        $totalHours = 0;

        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            if (AttendanceService::isWeekend($d)
                || AttendanceService::isHoliday($d)) {
                continue;
            }

            // A day only counts as absent once its shift-start time has passed
            $isPast = $d->lt($now->copy()->startOfDay());
            $isToday = $d->isSameDay($now);
            $canBeAbsent = $isPast || ($isToday && $now->gte($d->copy()->setTimeFrom($shiftStart)));
            if (! $canBeAbsent) {
                continue;
            }

            $totalDays++;
            $att = $attendances->get($d->format('Y-m-d'));
            if ($att) {
                if ($att->status === Attendance::STATUS_PRESENT) $present++;
                if ($att->status === Attendance::STATUS_LATE) $late++;
                $totalHours += $att->working_hours ?? 0;
            } else {
                $absent++;
            }
        }

        $stats = [
            'total_days' => $totalDays,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'total_hours' => round($totalHours, 1),
            'avg_hours' => $totalDays > 0 ? round($totalHours / $totalDays, 1) : 0,
        ];

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $canCheckout = $todayAttendance
            && ! $todayAttendance->logout_time
            && $todayAttendance->login_time;

        $monthLabel = $startOfMonth->format('F Y');

        return view('attendance.dashboard', compact(
            'calendar', 'stats', 'currentMonth', 'todayAttendance', 'canCheckout', 'monthLabel'
        ));
    }

    // ── Admin view ───────────────────────────────────────────────────

    /** Daily roster + counts for the selected day, plus a 7-day trend. */
    public function index(Request $request)
    {
        try {
            $today = Carbon::today();
            $startDate = $request->get('start_date', $today->format('Y-m-d'));
            $endDate = $request->get('end_date', $today->format('Y-m-d'));
            $searchName = $request->get('search_name');
            $searchStatus = $request->get('status');

            if ($startDate && ! strtotime($startDate)) {
                $startDate = $today->format('Y-m-d');
            }
            if ($endDate && ! strtotime($endDate)) {
                $endDate = $today->format('Y-m-d');
            }

            $allEmployees = $this->getTrackableUsers()->orderBy('name')->get();
            $totalEmployees = $allEmployees->count();

            $query = Attendance::with('user')->whereBetween('date', [$startDate, $endDate]);

            if ($searchName) {
                $query->whereHas('user', function ($q) use ($searchName) {
                    $q->where('name', 'LIKE', '%' . $searchName . '%');
                });
            }
            if ($searchStatus) {
                $query->where('status', $searchStatus);
            }

            $attendanceDetails = $query->orderBy('date', 'desc')->orderBy('login_time')->get();

            // Stats + absentees are computed for the selected start date
            $selectedDate = Carbon::parse($startDate);
            $selectedAttendances = Attendance::with('user')
                ->whereDate('date', $selectedDate)
                ->whereHas('user', function ($q) {
                    $q->where('role', '!=', User::ROLE_ADMIN);
                })
                ->get();

            $presentCount = $selectedAttendances->where('status', Attendance::STATUS_PRESENT)->count();
            $lateCount = $selectedAttendances->where('status', Attendance::STATUS_LATE)->count();

            $isWorkday = ! AttendanceService::isWeekend($selectedDate)
                && ! AttendanceService::isHoliday($selectedDate);
            $absentCount = $isWorkday ? max(0, $totalEmployees - $selectedAttendances->count()) : 0;

            $presentUserIds = $selectedAttendances->pluck('user_id')->toArray();
            $absentEmployees = $this->getTrackableUsers()->whereNotIn('id', $presentUserIds)->get();

            $weeklyStats = $this->getWeeklyStats();

            return view('attendance.index', compact(
                'totalEmployees', 'presentCount', 'lateCount', 'absentCount',
                'attendanceDetails', 'absentEmployees', 'allEmployees',
                'startDate', 'endDate', 'searchName', 'searchStatus', 'weeklyStats'
            ));
        } catch (\Exception $e) {
            Log::error('Attendance Index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading attendance data. Please try again.');
        }
    }

    /** Paginated attendance history with user/status/date filters. */
    public function history(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $userId = $request->get('user_id');
        $status = $request->get('status');

        $query = Attendance::with('user')->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('login_time', 'desc')->paginate(50)->withQueryString();

        $users = $this->getTrackableUsers()->orderBy('name')->get();
        $summaryStats = $this->getSummaryStats($startDate, $endDate, $userId);

        return view('attendance.history', compact(
            'attendances', 'users', 'startDate', 'endDate', 'userId', 'status', 'summaryStats'
        ));
    }

    // ── Admin edit (gated by attendance.edit) ────────────────────────

    /**
     * Create or update a single attendance record (admin correction).
     * Matches an existing row by id, or by (user, date) when adding one.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'id'          => ['nullable', 'integer', 'exists:attendances,id'],
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'date'        => ['required', 'date'],
            'status'      => ['required', Rule::in([
                Attendance::STATUS_PRESENT, Attendance::STATUS_LATE,
                Attendance::STATUS_ABSENT, Attendance::STATUS_LEAVE,
            ])],
            'login_time'  => ['nullable', 'date_format:H:i'],
            'logout_time' => ['nullable', 'date_format:H:i'],
        ]);

        $date = Carbon::parse($data['date'])->format('Y-m-d');

        $attendance = ! empty($data['id'])
            ? Attendance::findOrFail($data['id'])
            : Attendance::firstOrNew(['user_id' => $data['user_id'], 'date' => $date]);

        $isNew  = ! $attendance->exists;
        $before = [
            'status'      => $attendance->status,
            'login_time'  => optional($attendance->login_time)->format('H:i'),
            'logout_time' => optional($attendance->logout_time)->format('H:i'),
        ];

        $attendance->fill([
            'user_id'     => $data['user_id'],
            'date'        => $date,
            'status'      => $data['status'],
            'login_time'  => $data['login_time']  ? Carbon::parse($date . ' ' . $data['login_time'])  : null,
            'logout_time' => $data['logout_time'] ? Carbon::parse($date . ' ' . $data['logout_time']) : null,
        ]);
        $attendance->save(); // working_hours is recalculated in the model's saving() hook

        $staff = $attendance->user->name ?? ('user #' . $attendance->user_id);
        AuditLog::logAction(
            $isNew ? 'attendance_created' : 'attendance_updated',
            $request->user(),
            'Attendance',
            $attendance->id,
            ($isNew ? 'Added' : 'Edited') . ' attendance for ' . $staff . ' on '
                . Carbon::parse($date)->format('D, d M Y') . ' — ' . ucfirst($data['status']),
            ['before' => $before, 'after' => [
                'status'      => $attendance->status,
                'login_time'  => optional($attendance->login_time)->format('H:i'),
                'logout_time' => optional($attendance->logout_time)->format('H:i'),
            ]],
        );

        return redirect($request->input('return_to') ?: route('attendance.index'))
            ->with('success', 'Attendance saved for ' . $staff . '.');
    }

    /** Delete a wrongly-created attendance record (admin correction). */
    public function destroy(Request $request, Attendance $attendance)
    {
        $staff = $attendance->user->name ?? ('user #' . $attendance->user_id);
        $when  = $attendance->date->format('D, d M Y');
        $id    = $attendance->id;

        $attendance->delete();

        AuditLog::logAction(
            'attendance_deleted',
            $request->user(),
            'Attendance',
            $id,
            'Removed attendance for ' . $staff . ' on ' . $when,
        );

        return redirect($request->input('return_to') ?: route('attendance.index'))
            ->with('success', 'Attendance removed for ' . $staff . '.');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function getWeeklyStats()
    {
        $totalEmployees = $this->getTrackableUsers()->count();

        $weekDates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $weekDates->push(Carbon::now()->subDays($i));
        }

        return $weekDates->map(function ($date) use ($totalEmployees) {
            $dayAttendances = Attendance::whereDate('date', $date)
                ->whereHas('user', function ($q) {
                    $q->where('role', '!=', User::ROLE_ADMIN);
                })
                ->get();

            $isWorkday = ! AttendanceService::isWeekend($date)
                && ! AttendanceService::isHoliday($date);
            $absentCount = $isWorkday ? max(0, $totalEmployees - $dayAttendances->count()) : 0;

            return [
                'date' => $date->format('M d'),
                'present' => $dayAttendances->where('status', Attendance::STATUS_PRESENT)->count(),
                'late' => $dayAttendances->where('status', Attendance::STATUS_LATE)->count(),
                'absent' => $absentCount,
                'percentage' => $totalEmployees > 0
                    ? round(($dayAttendances->count() / $totalEmployees) * 100, 1) : 0,
            ];
        });
    }

    private function getSummaryStats($startDate, $endDate, $userId = null)
    {
        $query = Attendance::whereBetween('date', [$startDate, $endDate]);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $attendances = $query->get();

        return [
            'total_records' => $attendances->count(),
            'present' => $attendances->where('status', Attendance::STATUS_PRESENT)->count(),
            'late' => $attendances->where('status', Attendance::STATUS_LATE)->count(),
            'avg_login_time' => $this->calculateAverageLoginTime($attendances),
        ];
    }

    private function calculateAverageLoginTime($attendances)
    {
        $withLogin = $attendances->filter(fn ($a) => $a->login_time);
        if ($withLogin->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $withLogin->sum(function ($attendance) {
            $time = Carbon::parse($attendance->login_time);
            return $time->hour * 60 + $time->minute;
        });

        $avgMinutes = $totalMinutes / $withLogin->count();

        return sprintf('%02d:%02d', floor($avgMinutes / 60), $avgMinutes % 60);
    }
}
