<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log($user, $booking, string $action, string $description, $oldValues = null, $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'booking_id' => $booking->id ?? $booking->id ?? null,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
