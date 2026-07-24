<?php

// app/Services/AttendanceService.php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

/**
 * Attendance marking logic — ported from taurus-crm.
 *
 * Adaptations for this app: settings are read via Setting::getValue() (this
 * app's API), status values come from the Attendance model constants, and the
 * public-holiday check was dropped (no PublicHoliday model here). The office
 * device-fingerprint cookie is not tracked in this app, so it is left null.
 */
class AttendanceService
{
    /**
     * Whether a date falls on a non-working weekend day.
     *
     * Travel Orbit works Monday–Saturday, so Sunday is the only weekend / off
     * day. This is the single source of truth for the working-week rule —
     * change it here if the working week ever changes.
     */
    public static function isWeekend(Carbon $date): bool
    {
        return $date->dayOfWeek === Carbon::SUNDAY;
    }

    /**
     * Company-wide holidays, keyed by Y-m-d → name. Stored as a flat list in
     * settings (managed from Settings → Attendance), so no extra table.
     *
     * @return array<string, string>
     */
    public static function holidayMap(): array
    {
        $map = [];
        foreach (Setting::getValue('attendance_holidays', []) as $h) {
            if (! empty($h['date'])) {
                $map[$h['date']] = $h['name'] ?? 'Holiday';
            }
        }
        return $map;
    }

    /** Whether the given date is a company holiday. */
    public static function isHoliday($date): bool
    {
        $key = $date instanceof Carbon ? $date->format('Y-m-d') : (string) $date;
        return array_key_exists($key, self::holidayMap());
    }

    public function isInOfficeNetwork($ipAddress = null)
    {
        if (! Setting::getValue('attendance_enabled', true)) {
            return false;
        }

        $allowedNetworks = Setting::getValue('office_networks', []);

        if (is_string($allowedNetworks)) {
            $allowedNetworks = array_filter(array_map('trim', explode(',', $allowedNetworks)));
        }

        // No networks configured → no IP restriction; allow from any device
        if (empty($allowedNetworks)) {
            return true;
        }

        $ip = $ipAddress ?: Request::ip();

        foreach ($allowedNetworks as $network) {
            $network = trim($network);
            if ($this->ipInRange($ip, $network)) {
                return true;
            }
        }

        return false;
    }

    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    public function markAttendance($userId, $forceOffice = false)
    {
        $currentTime = Carbon::now();

        $officeStartTimeRaw = Setting::getValue('office_start_time', '09:00');
        $lateTimeRaw = Setting::getValue('late_time', '09:15');

        // Accept both '09:00' and '09:00 AM' formats
        try {
            $startTime = Carbon::createFromFormat('H:i', $officeStartTimeRaw);
        } catch (\Exception $e) {
            $startTime = Carbon::createFromFormat('h:i A', $officeStartTimeRaw);
        }

        try {
            $lateTime = Carbon::createFromFormat('H:i', $lateTimeRaw);
        } catch (\Exception $e) {
            $lateTime = Carbon::createFromFormat('h:i A', $lateTimeRaw);
        }

        $bufferHours = (int) Setting::getValue('attendance_buffer_hours', '1');
        $shiftDurationHours = (int) Setting::getValue('shift_duration_hours', '8');

        // windowStart allows early check-in up to bufferHours before start time
        $windowStart = $startTime->copy()->subHours($bufferHours);
        $windowEnd = $startTime->copy()->addHours($shiftDurationHours + $bufferHours);

        $isWithinOfficeHours = $currentTime->between($windowStart, $windowEnd, true);

        if (! $isWithinOfficeHours && ! $forceOffice) {
            return [
                'success' => false,
                'message' => 'Attendance can only be marked between ' . $windowStart->format('g:i A') . ' and ' . $windowEnd->format('g:i A') . '. Office hours are ' . $startTime->format('g:i A') . ' to ' . $startTime->copy()->addHours($shiftDurationHours)->format('g:i A') . ' with ' . $bufferHours . '-hour buffer.',
            ];
        }

        $shiftDate = Carbon::today();

        // Holiday guard — a day marked a holiday needs no attendance.
        if (self::isHoliday($shiftDate)) {
            return [
                'success' => false,
                'message' => 'Today is a holiday (' . self::holidayMap()[$shiftDate->format('Y-m-d')] . '). Attendance is not required.',
            ];
        }

        // Weekend guard (Travel Orbit works Mon–Sat, so this only blocks Sundays)
        if (! Setting::getValue('allow_weekend_attendance', false)) {
            if (self::isWeekend($shiftDate)) {
                return [
                    'success' => false,
                    'message' => 'Attendance marking is not allowed on Sundays.',
                ];
            }
        }

        // Office-network restriction is opt-in via the setting below.
        if (Setting::getValue('attendance_restrict_to_office', false) && ! $forceOffice && ! $this->isInOfficeNetwork()) {
            return [
                'success' => false,
                'message' => 'Attendance can only be marked from office network.',
                'debug_ip' => Request::ip(),
                'allowed_networks' => Setting::getValue('office_networks'),
            ];
        }

        // Already marked today?
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $shiftDate)
            ->first();

        if ($attendance) {
            return [
                'success' => false,
                'message' => 'Attendance already marked for today.',
                'attendance' => $attendance,
            ];
        }

        // Present if on time, late if past the late threshold
        $status = $currentTime->lessThanOrEqualTo($lateTime)
            ? Attendance::STATUS_PRESENT
            : Attendance::STATUS_LATE;

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $shiftDate,
            'login_time' => $currentTime,
            'ip_address' => Request::ip(),
            'device_fingerprint' => null,
            'device_name' => null,
            'status' => $status,
        ]);

        return [
            'success' => true,
            'message' => 'Attendance marked successfully.',
            'attendance' => $attendance,
            'status' => $status,
        ];
    }

    public function markLogout($userId)
    {
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', Carbon::today())
            ->whereNotNull('login_time')
            ->whereNull('logout_time')
            ->first();

        if ($attendance && ! $attendance->logout_time) {
            $attendance->update([
                'logout_time' => Carbon::now(),
            ]);

            return [
                'success' => true,
                'message' => 'Logout time recorded.',
                'attendance' => $attendance,
            ];
        }

        return [
            'success' => false,
            'message' => 'No active attendance found to check out.',
        ];
    }

    // Check-and-mark used by the login listener / dashboard visits.
    public function checkAndMarkDailyAttendance($userId)
    {
        $currentTime = Carbon::now();

        $officeStartTimeRaw = Setting::getValue('office_start_time', '09:00');
        try {
            $startTime = Carbon::createFromFormat('H:i', $officeStartTimeRaw);
        } catch (\Exception $e) {
            $startTime = Carbon::createFromFormat('h:i A', $officeStartTimeRaw);
        }

        $bufferHours = (int) Setting::getValue('attendance_buffer_hours', '1');
        $shiftDurationHours = (int) Setting::getValue('shift_duration_hours', '8');
        $windowStart = $startTime->copy()->subHours($bufferHours);
        $windowEnd = $startTime->copy()->addHours($shiftDurationHours + $bufferHours);

        $isWithinOfficeHours = $currentTime->between($windowStart, $windowEnd, true);

        if (! $isWithinOfficeHours) {
            return [
                'success' => false,
                'message' => 'Attendance can only be marked between ' . $windowStart->format('g:i A') . ' and ' . $windowEnd->format('g:i A') . '.',
            ];
        }

        $shiftDate = Carbon::today();

        $existingAttendance = Attendance::where('user_id', $userId)
            ->where('date', $shiftDate)
            ->first();

        if ($existingAttendance) {
            return [
                'success' => false,
                'message' => 'Attendance already marked for today.',
                'attendance' => $existingAttendance,
            ];
        }

        // Auto-mark unless office-only restriction is enabled and the user is off-network.
        if (! Setting::getValue('attendance_restrict_to_office', false) || $this->isInOfficeNetwork()) {
            return $this->markAttendance($userId);
        }

        return [
            'success' => false,
            'message' => 'Not in office network, attendance not marked.',
            'should_show_manual' => true,
        ];
    }

    /**
     * Auto-checkout employees who haven't checked out by end of day.
     * Intended to be wired to a scheduled command.
     */
    public function autoCheckoutOverdueAttendances()
    {
        $currentTime = Carbon::now();

        // Only run shortly after the end of the shift
        if ($currentTime->hour < 17 || $currentTime->hour > 18) {
            return [
                'success' => false,
                'message' => 'Auto-checkout only runs between 5:00 PM and 6:59 PM.',
            ];
        }

        $shiftDate = Carbon::today();

        $overdueAttendances = Attendance::where('date', $shiftDate)
            ->whereNull('logout_time')
            ->get();

        $checkedOutCount = 0;

        foreach ($overdueAttendances as $attendance) {
            $checkoutTime = Carbon::today()->setTime(17, 30, 0);

            $attendance->update([
                'logout_time' => $checkoutTime,
                'auto_checkout' => true,
            ]);

            $checkedOutCount++;
        }

        return [
            'success' => true,
            'message' => "Auto-checkout completed for {$checkedOutCount} employee(s).",
            'checked_out_count' => $checkedOutCount,
        ];
    }
}
