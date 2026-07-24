<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily attendance record — ported from taurus-crm.
 *
 * Taurus-specific deps were stripped on the way over: status strings are
 * inlined here (was App\Support\Statuses) and public-holiday awareness was
 * dropped (no PublicHoliday model in this app — weekends are still skipped).
 */
class Attendance extends Model
{
    // ── Status values ────────────────────────────────────────────────
    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE    = 'late';
    public const STATUS_ABSENT  = 'absent';
    public const STATUS_LEAVE   = 'leave';

    protected $fillable = [
        'user_id',
        'date',
        'login_time',
        'logout_time',
        'ip_address',
        'device_fingerprint',
        'device_name',
        'status',
        'working_hours',
        'auto_checkout',
    ];

    protected $casts = [
        'date' => 'date',
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'auto_checkout' => 'boolean',
    ];

    /**
     * Boot method to auto-calculate working hours
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            // Auto-calculate working hours if both times exist
            if ($attendance->login_time && $attendance->logout_time) {
                try {
                    // Use the actual attendance date for calculation, not today's date
                    $attendanceDate = $attendance->date ?? Carbon::today();

                    // Parse login and logout times with the actual attendance date
                    $loginTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $attendance->login_time->format('H:i:s'));
                    $logoutTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $attendance->logout_time->format('H:i:s'));

                    $attendance->working_hours = round($loginTime->diffInHours($logoutTime, true), 1);
                } catch (\Exception $e) {
                    // If parsing fails, set working hours to 0
                    $attendance->working_hours = 0;
                }
            } else {
                // No logout time means 0 working hours
                $attendance->working_hours = 0;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if user is late based on office start time setting
    public function isLate()
    {
        if (! $this->login_time) {
            return false;
        }

        $officeStartTimeRaw = Setting::getValue('office_start_time', '09:00');
        $lateThreshold = (int) Setting::getValue('late_threshold_minutes', 15);

        try {
            $startTime = Carbon::createFromFormat('H:i', $officeStartTimeRaw);
        } catch (\Exception $e) {
            $startTime = Carbon::createFromFormat('h:i A', $officeStartTimeRaw);
        }
        $lateTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $startTime->copy()->addMinutes($lateThreshold)->format('H:i:s'));
        $loginTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->login_time->format('H:i:s'));

        return $loginTime->greaterThan($lateTime);
    }

    // Calculate working hours
    public function getWorkingHoursAttribute()
    {
        if (! $this->login_time || ! $this->logout_time) {
            return 0;
        }

        // Use the actual attendance date for calculation
        $attendanceDate = $this->date ?? Carbon::today();

        // Extract time from login_time and logout_time (which may have been cast with today's date)
        $loginTimeStr = $this->login_time instanceof \DateTime
            ? $this->login_time->format('H:i:s')
            : (is_string($this->login_time) ? $this->login_time : '00:00:00');

        $logoutTimeStr = $this->logout_time instanceof \DateTime
            ? $this->logout_time->format('H:i:s')
            : (is_string($this->logout_time) ? $this->logout_time : '00:00:00');

        // Parse login and logout times with the ACTUAL attendance date
        $loginTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $loginTimeStr);
        $logoutTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $logoutTimeStr);

        return round($loginTime->diffInHours($logoutTime, true), 1);
    }

    /**
     * Get current working hours (live calculation if still working)
     */
    public function getCurrentWorkingHours()
    {
        if (! $this->login_time) {
            return 0;
        }

        if ($this->logout_time) {
            $endTime = Carbon::parse($this->logout_time);
            $startTime = Carbon::parse($this->login_time);

            return round($startTime->diffInHours($endTime, true), 1);
        }

        // For records without logout, only show live hours if it's today
        if (! $this->date->isToday()) {
            return 0;
        }

        $endTime = Carbon::now();
        $startTime = Carbon::parse($this->login_time);

        return round($startTime->diffInHours($endTime, true), 1);
    }

    /**
     * Get current working hours formatted as "Xh Ym" string
     */
    public function getFormattedCurrentWorkingHours()
    {
        if (! $this->login_time) {
            return '-';
        }

        if ($this->logout_time) {
            $endTime = Carbon::parse($this->logout_time);
            $startTime = Carbon::parse($this->login_time);
            $totalMinutes = $startTime->diffInMinutes($endTime);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            return "{$hours}h {$minutes}m";
        }

        if (! $this->date->isToday()) {
            return 'Incomplete';
        }

        $endTime = Carbon::now();
        $startTime = Carbon::parse($this->login_time);
        $totalMinutes = $startTime->diffInMinutes($endTime);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Check if employee is still working (no logout time)
     */
    public function isStillWorking()
    {
        return $this->login_time && ! $this->logout_time;
    }

    public function getFormattedLoginTimeAttribute()
    {
        return $this->login_time ? $this->login_time->format('g:i A') : 'N/A';
    }

    public function getFormattedLogoutTimeAttribute()
    {
        return $this->logout_time ? $this->logout_time->format('g:i A') : 'N/A';
    }

    public function isFullWorkingDay()
    {
        return $this->working_hours >= 8;
    }

    // ── Query scopes ─────────────────────────────────────────────────
    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeAbsent($query)
    {
        return $query->whereIn('status', [self::STATUS_ABSENT, self::STATUS_LEAVE]);
    }

    public function scopeLate($query)
    {
        $officeStartTimeRaw = Setting::getValue('office_start_time', '09:00');
        $lateThreshold = (int) Setting::getValue('late_threshold_minutes', 15);

        try {
            $startTime = Carbon::createFromFormat('H:i', $officeStartTimeRaw);
        } catch (\Exception $e) {
            $startTime = Carbon::createFromFormat('h:i A', $officeStartTimeRaw);
        }
        $lateTimeStr = $startTime->copy()->addMinutes($lateThreshold)->format('H:i:s');

        return $query->whereRaw("TIME(login_time) > '$lateTimeStr'");
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Attendance summary for a user in a given month. Weekends are excluded;
     * public holidays are NOT modelled in this app so every non-weekend day
     * up to today counts as a workday.
     */
    public static function getAttendanceSummary($userId, $month, $year)
    {
        $records = self::forUser($userId)->forMonth($month, $year)->get()
            ->keyBy(function ($a) { return $a->date->format('Y-m-d'); });

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $workdays = 0;
        $present = 0;
        $late = 0;
        $absent = 0;
        $totalHours = 0;

        $cursor = $startOfMonth->copy();
        $now = Carbon::now();

        while ($cursor->lte($endOfMonth)) {
            // Skip future dates
            if ($cursor->gt($now)) {
                $cursor->addDay();
                continue;
            }

            // Skip weekends (Sunday only — Saturday is a working day) and holidays
            if (\App\Services\AttendanceService::isWeekend($cursor)
                || \App\Services\AttendanceService::isHoliday($cursor)) {
                $cursor->addDay();
                continue;
            }

            $workdays++;
            $att = $records->get($cursor->format('Y-m-d'));

            if ($att) {
                if ($att->status === self::STATUS_PRESENT) $present++;
                if ($att->status === self::STATUS_LATE) $late++;
                $totalHours += $att->working_hours ?? 0;
            } else {
                $absent++;
            }

            $cursor->addDay();
        }

        return [
            'total_days' => $workdays,
            'present_days' => $present,
            'late_days' => $late,
            'absent_days' => $absent,
            'total_working_hours' => round($totalHours, 1),
            'average_working_hours' => $workdays > 0 ? round($totalHours / $workdays, 1) : 0,
        ];
    }

    public static function hasRecordForDate($userId, $date)
    {
        return self::where('user_id', $userId)->where('date', $date)->exists();
    }

    /**
     * Status → Bootstrap badge class map for UI. Replaces taurus's CSS-var
     * approach (which relied on _root.scss custom properties not present here).
     */
    public const STATUS_BADGES = [
        self::STATUS_PRESENT => 'bg-label-success',
        self::STATUS_LATE    => 'bg-label-warning',
        self::STATUS_ABSENT  => 'bg-label-danger',
        self::STATUS_LEAVE   => 'bg-label-info',
    ];

    public const STATUS_BADGE_DEFAULT = 'bg-label-secondary';

    public function getStatusWithColorAttribute()
    {
        $status = $this->status;
        if ($status === self::STATUS_PRESENT && $this->isLate()) {
            $status = self::STATUS_LATE;
        }

        return [
            'status' => $status,
            'badge'  => self::STATUS_BADGES[$status] ?? self::STATUS_BADGE_DEFAULT,
            'label'  => ucfirst($status),
        ];
    }
}
