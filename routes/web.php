<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicPropertyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitRequestController;
use Illuminate\Support\Facades\Route;

// --- Public site (no login required) ---
Route::get('/', [PublicPropertyController::class, 'index'])->name('public.properties.index');
Route::get('/browse/{property}', [PublicPropertyController::class, 'show'])->name('public.properties.show');
Route::post('/browse/{property}/visit-request', [VisitRequestController::class, 'store'])->name('public.visit-requests.store');

// --- Guest / auth ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// --- Authenticated app ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Properties — index/show open to admin+owner+tenant (scoped inside controller),
    // create/edit/update/destroy restricted to admin inside the controller.
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

    // Property images are managed by the Livewire PropertyImageManager component (see properties/show.blade.php)

    // Leases — assign / end (admin only, enforced in controller)
    Route::get('/properties/{property}/lease/create', [LeaseController::class, 'create'])->name('leases.create');
    Route::post('/properties/{property}/lease', [LeaseController::class, 'store'])->name('leases.store');
    Route::post('/leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/leases/{lease}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/leases/{lease}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/my-payments/submit', [PaymentController::class, 'submitForm'])->name('payments.submit-form');
    Route::post('/my-payments/submit', [PaymentController::class, 'submit'])->name('payments.submit');
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Issues
    Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/create', [IssueController::class, 'create'])->name('issues.create');
    Route::post('/issues', [IssueController::class, 'store'])->name('issues.store');
    Route::get('/issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
    Route::put('/issues/{issue}', [IssueController::class, 'update'])->name('issues.update');

    // Users (admin only, enforced in controller)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    // Visit requests (public creates them; admin manages status)
    Route::put('/visit-requests/{visitRequest}/status', [VisitRequestController::class, 'updateStatus'])->name('visit-requests.update-status');
});
