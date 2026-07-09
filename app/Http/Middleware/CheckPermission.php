<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Passes if the user is an admin (hard-wired super-user) or holds ANY of
     * the listed permission keys. Mirrors CheckRole's redirect-on-deny UX.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!$user->hasAnyPermission($permissions)) {
            return redirect()->route('dashboard')->with('error', 'Access Denied. You do not have permission to access this page.');
        }

        return $next($request);
    }
}
