<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adapted from Taurus CRM - logs login IP/timestamp on first request per session.
 */
class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !session()->has('activity_logged')) {
            $user = auth()->user();

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::logAction(
                action:      'login',
                user:        $user,
                model:       'User',
                model_id:    $user->id,
                description: "Logged in from {$request->ip()}",
            );

            session()->put('activity_logged', true);
        }

        return $next($request);
    }
}
