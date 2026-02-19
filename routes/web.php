<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\WorkingHourController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\{PackageController, BookingController};
use App\Http\Controllers\Admin\{BookingController as PublicBookingController};
use App\Http\Middleware\{logoutCheck, loginCheck};

// ================================
// PUBLIC ROUTES (Customer Facing)
// ================================
Route::get('/', function () {
    return view('index');
});

Route::get('/booking-calendar', [CalendarController::class, 'index'])->name('booking.calendar');
Route::get('/calendar/getBookings', [CalendarController::class, 'getBookings'])->name('booking.getBookings');

// web.php
Route::post('/booking/initiate-payment', [PublicBookingController::class, 'initiatePayment'])->name('booking.initiate');
Route::any('/tap/callback', [PublicBookingController::class, 'tapCallback'])->name('tap.callback');
Route::any('/tap/redirect', [PublicBookingController::class, 'handleRedirect'])->name('tap.redirect');
Route::get('/tap/success', function() { return view('tap.success'); })->name('tap.success');
Route::get('/tap/failed', function() { return view('tap.failed'); })->name('tap.failed');

// Customer booking routes
Route::get('/packages', [PublicBookingController::class, 'index'])->name('packages.index');
Route::get('/book/{slug}', [PublicBookingController::class, 'show'])->name('book.package');
Route::post('/bookings/store', [PublicBookingController::class, 'store'])->name('bookings.store');

// ================================
// ADMIN AUTH ROUTES
// ================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'loginPage'])->name('login')->middleware([logoutCheck::class]);
    Route::post('login', [AdminAuthController::class, 'login']);
});

// ================================
// ADMIN PROTECTED ROUTES (All CRUDs)
// ================================
Route::prefix('admin')->middleware([loginCheck::class])->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // Existing CRUDs
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('updateProfile', [UserController::class, 'updateProfile'])->name('updateProfile');
    
    // Role Permissions
    Route::get('roles/{id}/permissions', [RoleController::class, 'editPermissions'])->name('permissions.edit');
    Route::put('roles/{id}/permissions/update', [RoleController::class, 'updatePermissions'])->name('permissions.update');
    
    // NEW BOOKING SYSTEM CRUDs
    Route::resource('packages', PackageController::class);
    Route::resource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('admin.bookings.status');
    Route::resource('working-hours', WorkingHourController::class);
    Route::resource('coupons', CouponController::class);
    
    // Reports (Future)
    Route::get('reports', [DashboardController::class, 'reports'])->name('reports.index');
    Route::get('reports/daily', [DashboardController::class, 'dailyReport'])->name('reports.daily');

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/export', [PaymentController::class, 'export'])->name('export');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::patch('/{id}/status', [PaymentController::class, 'updateStatus'])->name('update-status');
    });
});

// ================================
// API ROUTES (Future Payment Callbacks)
// ================================
Route::prefix('api')->name('api.')->group(function () {
    Route::post('tap/callback', [PublicBookingController::class, 'tapCallback'])->name('tap.callback');
});
