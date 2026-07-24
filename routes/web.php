<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\BookingController;
use App\Http\Controllers\Crm\BookingWorkflowController;
use App\Http\Controllers\EticketController;
use App\Http\Controllers\Finance\RefundController;
use App\Http\Controllers\Mis\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Settings\UserController as UserManagementController;
use App\Http\Controllers\CallCenter\CallCenterController;
use App\Http\Controllers\AttendanceController;

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
    Route::get('/accounts-dashboard', [DashboardController::class, 'accountsDashboardPage'])->name('accounts.dashboard')->middleware('permission:accounts.access');
    Route::get('/issuance-dashboard', [DashboardController::class, 'issuanceDashboardPage'])->name('issuance.dashboard')->middleware('permission:issuance.access');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

    // All Bookings — company-wide list
    Route::middleware('permission:bookings.view_all')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    });

    // Issued-but-unpaid mini reports — the user's own bookings, split out of the dashboard tabs
    Route::middleware('permission:bookings.own_issued')->group(function () {
        Route::get('/my-bookings/payment-plan', [DashboardController::class, 'paymentPlanReport'])->name('bookings.payment-plan');
        Route::get('/my-bookings/payment-awaiting', [DashboardController::class, 'paymentAwaitingReport'])->name('bookings.payment-awaiting');
    });

    // Booking create / edit / workflow — each action gated by its own permission.
    // "create" must be registered before the /bookings/{booking} wildcard below.
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create')->middleware('permission:bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store')->middleware('permission:bookings.create');
    Route::get('/bookings/{booking}/edit', fn($booking) => redirect()->route('bookings.show', $booking));
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware('permission:bookings.delete');

    Route::post('/bookings/{booking}/queue-issuance', [BookingWorkflowController::class, 'queueForIssuance'])->name('bookings.queue-issuance')->middleware('permission:bookings.queue_issuance');
    Route::post('/bookings/{booking}/invoice',        [BookingWorkflowController::class, 'invoice'])->name('bookings.invoice')->middleware('permission:payments.invoice');

    // Booking show — any authenticated user; the issuance/payment workflow POSTs below
    // are additionally authorised per-action by BookingPolicy in the controller.
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/remove-issuance',   [BookingWorkflowController::class, 'removeFromIssuanceQueue'])->name('bookings.remove-issuance')->middleware('permission:issuance.manage');
    Route::post('/bookings/{booking}/ticket-in-process', [BookingWorkflowController::class, 'markTicketInProcess'])->name('bookings.ticket-in-process')->middleware('permission:issuance.manage');
    Route::post('/bookings/{booking}/restore-pending',   [BookingWorkflowController::class, 'restoreToPending'])->name('bookings.restore-pending')->middleware('permission:issuance.manage');
    Route::post('/bookings/{booking}/issue',             [BookingWorkflowController::class, 'issue'])->name('bookings.issue')->middleware('permission:payments.issue');

    // E-ticket builder — a standalone print tool, not tied to any booking's page.
    // Pick a booking from the dropdown to prefill the form; nothing here saves back.
    Route::middleware('permission:eticket.access')->group(function () {
        Route::get('/eticket', [EticketController::class, 'index'])->name('eticket.builder');
        Route::get('/eticket/data/{booking}', [EticketController::class, 'data'])->name('eticket.data');
    });

    // Status Change — issuance's manual override for a booking sitting in the
    // issuance pipeline. Any status may be set; the matching date stamp and the
    // activity/audit entries are applied by BookingStatusService.
    Route::get('/status-change', fn() => view('content.issuance.status-change'))
        ->name('issuance.status-change')
        ->middleware('permission:bookings.change_status');

    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('/customers/{phone}', [CustomerController::class, 'show'])->name('customers.show');
    });

    Route::middleware('permission:accounts.access')->group(function () {
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds');
        Route::get('/payment-charges', fn() => view('content.finance.payment-charge-requests'))->name('payment-charges');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports',             fn() => view('content.reports.index'))->name('reports');
        Route::get('/reports/sales',       [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/payment-status', [DashboardController::class, 'paymentStatusReport'])->name('reports.payment-status');
    });

    // Departure/Arrival — reports.view holders (managers/accounts) see every
    // booking; agents/operations reach it via reports.departure_arrival and are
    // scoped to their own bookings inside the controller (canViewAllData gate).
    Route::middleware('permission:reports.view,reports.departure_arrival')->group(function () {
        Route::get('/reports/departure-arrival', [DashboardController::class, 'departureArrivalReport'])->name('reports.departure-arrival');
    });

    // Performance report — reports.performance_all holders see every agent;
    // reports.performance holders are scoped to their own data inside the
    // AgentPerformance Livewire component.
    Route::middleware('permission:reports.performance,reports.performance_all')->group(function () {
        Route::get('/reports/performance', [ReportController::class, 'performance'])->name('reports.performance');
    });

    // Settings — each sub-area is gated by its own delegatable permission.
    // Admin is a hard-wired super-user and passes every one of these.
    // The Settings Hub opens for anyone holding at least one Settings key.
    Route::middleware('permission:settings.users,settings.activity,settings.vendors,settings.gds,settings.ip,settings.attendance')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    });
    Route::middleware('permission:settings.users')->group(function () {
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
    Route::middleware('permission:settings.activity')->group(function () {
        Route::get('/settings/audit-log', [SettingsController::class, 'auditLog'])->name('settings.audit-log');
        Route::get('/settings/activity',  [SettingsController::class, 'auditLog'])->name('settings.activity');
    });
    Route::get('/settings/vendors', [SettingsController::class, 'vendors'])->name('settings.vendors')->middleware('permission:settings.vendors');
    Route::get('/settings/gds', [SettingsController::class, 'gds'])->name('settings.gds')->middleware('permission:settings.gds');
    Route::get('/settings/ip-whitelist', fn() => view('settings.ip-whitelist'))->name('settings.ip')->middleware('permission:settings.ip');
    Route::get('/settings/attendance', [SettingsController::class, 'attendance'])->name('settings.attendance')->middleware('permission:settings.attendance');

    // Call Desk — gated by calldesk.access; managers/admins see all agents' data,
    // everyone else is scoped to their own (enforced in the Livewire components).
    Route::middleware('permission:calldesk.access')->prefix('call-desk')->name('calldesk.')->group(function () {
        Route::get('/', [CallCenterController::class, 'dashboard'])->name('dashboard');
        Route::get('/new-call', [CallCenterController::class, 'newCall'])->name('new-call');
        Route::get('/inquiries', [CallCenterController::class, 'inquiries'])->name('inquiries');
        Route::get('/callbacks', [CallCenterController::class, 'callbacks'])->name('callbacks');
    });

    // Legacy admin-only Call Center URLs, kept as redirects to the new Call Desk.
    Route::redirect('/call-center', '/call-desk', 301);
    Route::redirect('/call-center/new-call', '/call-desk/new-call', 301);
    Route::redirect('/call-center/inquiries', '/call-desk/inquiries', 301);
    Route::redirect('/call-center/callbacks', '/call-desk/callbacks', 301);

    // Attendance — self-service for every user; the roster/history are gated
    // by attendance.view. Ported from taurus-crm.
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Self-service: any authenticated user marks their own attendance.
        Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('checkin');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('checkout');

        // Admin view: company-wide roster & history.
        Route::middleware('permission:attendance.view')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/history', [AttendanceController::class, 'history'])->name('history');
        });

        // Admin edit: correct / add / remove records. Gated by attendance.edit.
        Route::middleware('permission:attendance.edit')->group(function () {
            Route::post('/save', [AttendanceController::class, 'save'])->name('save');
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy')->whereNumber('attendance');
        });
    });
});
