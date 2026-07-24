<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

/**
 * Auto-marks attendance when a user logs in — ported from taurus-crm.
 *
 * Registered via Laravel 12 event auto-discovery (the handle() type-hint on
 * the Login event is enough; no EventServiceProvider needed). The god-mode
 * admin is untracked and skipped.
 */
class MarkAttendanceOnLogin
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        if (! $this->shouldMarkAttendance($user)) {
            return;
        }

        $result = $this->attendanceService->markAttendance($user->id);

        if ($result['success']) {
            session()->flash('attendance_success', $result['message']);
        } else {
            session()->flash('attendance_info', $result['message']);
        }

        Log::info('Attendance marking attempt', [
            'user_id' => $user->id,
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    private function shouldMarkAttendance(User $user): bool
    {
        // The god-mode admin operates untracked.
        return ! $user->isGodMode();
    }
}
