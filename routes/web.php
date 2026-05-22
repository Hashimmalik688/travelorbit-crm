<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\BookingController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\RefundController;
use App\Http\Controllers\Mis\ReportController;
use App\Http\Controllers\SettingsController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('role:admin,manager,operations,agent')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    });

    Route::middleware('role:admin,manager,operations,agent')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('/customers/{phone}', [CustomerController::class, 'show'])->name('customers.show');
    });

    Route::middleware('role:admin,manager,accounting')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds');
    });

    Route::middleware('role:admin,manager,accounting')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/performance', [ReportController::class, 'performance'])->name('reports.performance');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
        Route::get('/settings/audit-log', [SettingsController::class, 'auditLog'])->name('settings.audit-log');
    });

    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
});
