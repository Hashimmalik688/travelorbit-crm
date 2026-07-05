<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\BookingController;
use App\Http\Controllers\Crm\BookingWorkflowController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\RefundController;
use App\Http\Controllers\Mis\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Settings\UserController as UserManagementController;
use App\Http\Controllers\CallCenter\CallCenterController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/agent-dashboard',    [DashboardController::class, 'agentDashboardPage'])->name('agent.dashboard');
    Route::get('/my-dashboard', function () {
        $role = Auth::user()?->role;
        return match ($role) {
            'accounts'           => redirect()->route('accounts.dashboard'),
            'issuance'           => redirect()->route('issuance.dashboard'),
            default              => redirect()->route('agent.dashboard'),
        };
    })->name('my.dashboard');
    Route::get('/accounts-dashboard', [DashboardController::class, 'accountsDashboardPage'])->name('accounts.dashboard')->middleware('role:accounts,admin,manager');
    Route::get('/issuance-dashboard', [DashboardController::class, 'issuanceDashboardPage'])->name('issuance.dashboard')->middleware('role:issuance,admin,manager');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

    // All Bookings — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    });

    // My Bookings — not needed by agents, who only create + view their own via the dashboard
    Route::middleware('role:admin,manager,operations,accounts')->group(function () {
        Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.mine');
    });

    Route::middleware('role:admin,manager,operations,agent,accounts')->group(function () {
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/edit', fn($booking) => redirect()->route('bookings.show', $booking));
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

        Route::post('/bookings/{booking}/queue-issuance', [BookingWorkflowController::class, 'queueForIssuance'])->name('bookings.queue-issuance');
        Route::post('/bookings/{booking}/invoice',        [BookingWorkflowController::class, 'invoice'])->name('bookings.invoice');
    });

    // Booking show + issuance workflow — also accessible by issuance role
    Route::middleware('role:issuance,admin,manager,operations,agent,accounts')->group(function () {
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

        Route::post('/bookings/{booking}/remove-issuance',   [BookingWorkflowController::class, 'removeFromIssuanceQueue'])->name('bookings.remove-issuance');
        Route::post('/bookings/{booking}/ticket-in-process', [BookingWorkflowController::class, 'markTicketInProcess'])->name('bookings.ticket-in-process');
        Route::post('/bookings/{booking}/restore-pending',   [BookingWorkflowController::class, 'restoreToPending'])->name('bookings.restore-pending');
        Route::post('/bookings/{booking}/issue',             [BookingWorkflowController::class, 'issue'])->name('bookings.issue');
    });

    Route::middleware('role:admin,manager,operations,agent,accounts')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('/customers/{phone}', [CustomerController::class, 'show'])->name('customers.show');
    });

    Route::middleware('role:admin,manager,accounts')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds');
        Route::get('/payment-charges', fn() => view('content.finance.payment-charge-requests'))->name('payment-charges');
    });

    Route::middleware('role:admin,manager,accounts')->group(function () {
        Route::get('/reports',             fn() => view('content.reports.index'))->name('reports');
        Route::get('/reports/sales',       [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/performance', [ReportController::class, 'performance'])->name('reports.performance');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        // User management (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/settings/users',                  [UserManagementController::class, 'index'])->name('settings.users');
            Route::get('/settings/users/index',            [UserManagementController::class, 'index'])->name('settings.users.index');
            Route::get('/settings/users/create',           [UserManagementController::class, 'create'])->name('settings.users.create');
            Route::post('/settings/users',                 [UserManagementController::class, 'store'])->name('settings.users.store');
            Route::get('/settings/users/{user}',           [UserManagementController::class, 'show'])->name('settings.users.show');
            Route::get('/settings/users/{user}/edit',      [UserManagementController::class, 'edit'])->name('settings.users.edit');
            Route::put('/settings/users/{user}',           [UserManagementController::class, 'update'])->name('settings.users.update');
            Route::delete('/settings/users/{user}',        [UserManagementController::class, 'destroy'])->name('settings.users.destroy');
            Route::post('/settings/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('settings.users.reset-password');
        });
        Route::get('/settings/audit-log', [SettingsController::class, 'auditLog'])->name('settings.audit-log');
        Route::get('/settings/activity',  [SettingsController::class, 'auditLog'])->name('settings.activity');
        Route::get('/settings/vendors',   [SettingsController::class, 'vendors'])->name('settings.vendors');
        Route::get('/settings/gds', [SettingsController::class, 'gds'])->name('settings.gds');
        Route::get('/settings/ip-whitelist', fn() => view('settings.ip-whitelist'))->name('settings.ip')->middleware('role:admin');
    });

    Route::middleware('role:admin')->prefix('call-center')->name('callcenter.')->group(function () {
        Route::get('/', [CallCenterController::class, 'dashboard'])->name('dashboard');
        Route::get('/new-call', [CallCenterController::class, 'newCall'])->name('new-call');
        Route::get('/inquiries', [CallCenterController::class, 'inquiries'])->name('inquiries');
        Route::get('/callbacks', [CallCenterController::class, 'callbacks'])->name('callbacks');
    });
});
