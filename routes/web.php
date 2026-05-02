<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeaseController as AdminLeaseController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\UnitController as AdminUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\ChangePasswordController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\NotificationController as TenantNotificationController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('tenant.dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('properties', AdminPropertyController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/properties/{property}', [AdminPropertyController::class, 'show'])
            ->name('properties.show');
        Route::post('/properties/{property}/images', [AdminPropertyController::class, 'uploadImages'])
            ->name('properties.images.upload');
        Route::delete('/properties/{property}/images/{image}', [AdminPropertyController::class, 'deleteImage'])
            ->name('properties.images.delete');

        Route::resource('units', AdminUnitController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/units/{unit}', [AdminUnitController::class, 'show'])
            ->name('units.show');
        Route::post('/units/{unit}/images', [AdminUnitController::class, 'uploadImages'])
            ->name('units.images.upload');
        Route::delete('/units/{unit}/images/{image}', [AdminUnitController::class, 'deleteImage'])
            ->name('units.images.delete');
        Route::resource('tenants', AdminTenantController::class)->except(['create', 'edit']);
        Route::resource('leases', AdminLeaseController::class)->except(['create', 'edit']);
        Route::post('/leases/{lease}/contract', [AdminLeaseController::class, 'uploadContract'])
            ->name('leases.contract.upload');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::patch('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}/redirect', [AdminNotificationController::class, 'markAsReadAndRedirect'])->name('notifications.redirect');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    });

    Route::middleware(['role:tenant'])->group(function () {
        Route::get('/tenant/change-password', [ChangePasswordController::class, 'show'])
            ->name('password.change');

        Route::post('/tenant/change-password', [ChangePasswordController::class, 'update'])
            ->name('password.change.update');
    });

    Route::middleware(['role:tenant', 'password.change'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
        Route::get('/payments', [TenantPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [TenantPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/proof', [TenantPaymentController::class, 'submitProof'])->name('payments.submitProof');
        Route::get('/notifications', [TenantNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}/redirect', [TenantNotificationController::class, 'markAsReadAndRedirect'])->name('notifications.redirect');
        Route::patch('/notifications/read-all', [TenantNotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::patch('/notifications/{id}/read', [TenantNotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    });
});

require __DIR__.'/auth.php';
