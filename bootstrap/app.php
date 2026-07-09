<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {

    // ── Named middleware aliases ─────────────────────────────────────
    $middleware->alias([
        'role'       => \App\Http\Middleware\CheckRole::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'throttle'   => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ]);

    // ── Global web middleware stack ──────────────────────────────────
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,   // X-Frame, CSP, HSTS etc.
        \App\Http\Middleware\BotDetection::class,      // Block scanners & crawlers
        \App\Http\Middleware\LogUserActivity::class,   // Track login IP/time
    ]);

  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        if ($e->getStatusCode() === 419) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your session expired. Please log in again.']);
        }
    });
  })->create();
