<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log($user, $booking, string $action, string $description, $oldValues = null, $newValues = null): ?AuditLog
    {
        try {
            return AuditLog::create([
                'user_id'     => $user?->id,
                'user_email'  => $user?->email ?? 'system',
                'action'      => $action,
                'model'       => 'Booking',
                'model_id'    => $booking?->id,
                'description' => $description,
                'changes'     => $oldValues || $newValues ? ['before' => $oldValues, 'after' => $newValues] : null,
                'ip_address'  => request()->ip(),
                'user_agent'  => substr(request()->userAgent() ?? '', 0, 500),
            ]);
        } catch (\Throwable) {
            // Never let audit logging crash the app
            return null;
        }
    }
}
