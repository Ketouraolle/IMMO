<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicPropertyController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitRequestController;
use Illuminate\Support\Facades\Route;

// --- Public site (no login required) ---
// Visit booking happens inside the VisitBooking Livewire component on the listing page.
Route::get('/', [PublicPropertyController::class, 'index'])->name('public.properties.index');
Route::get('/browse/{property}', [PublicPropertyController::class, 'show'])->name('public.properties.show');

// --- Language switch (everyone) ---
Route::get('/locale/{locale}', LocaleController::class)->whereIn('locale', ['en', 'fr'])->name('locale.switch');

// --- Guest / auth ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    // Second step for accounts with two-factor authentication
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:6,1');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// --- Authenticated app ---
Route::middleware('auth')->group(function () {
    // Security settings stay reachable while an admin still has to set up two-factor authentication
    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
    Route::post('/security/two-factor', [SecurityController::class, 'enable'])->name('security.two-factor.enable');
    Route::post('/security/two-factor/confirm', [SecurityController::class, 'confirm'])->name('security.two-factor.confirm');
    Route::delete('/security/two-factor', [SecurityController::class, 'disable'])->name('security.two-factor.disable');
    Route::post('/security/two-factor/recovery-codes', [SecurityController::class, 'regenerateRecoveryCodes'])->name('security.two-factor.recovery-codes');

    Route::middleware('two-factor.setup')->group(function () {
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

        // Property images and visit dates are managed by Livewire components on properties/show.blade.php

        // Leases — assign / end (admin only, enforced in controller)
        Route::get('/properties/{property}/lease/create', [LeaseController::class, 'create'])->name('leases.create');
        Route::post('/properties/{property}/lease', [LeaseController::class, 'store'])->name('leases.store');
        Route::post('/leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');

        // Contracts — admin prepares and sends, tenant reads and signs
        Route::get('/leases/{lease}/contract', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('/leases/{lease}/contract', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('/my-contract', [ContractController::class, 'mine'])->name('contracts.mine');
        Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::post('/contracts/{contract}/send', [ContractController::class, 'send'])->name('contracts.send');
        Route::post('/contracts/{contract}/sign', [ContractController::class, 'sign'])->name('contracts.sign');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/export', [PaymentController::class, 'export'])->name('payments.export');
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
        Route::delete('/users/{user}/two-factor', [UserController::class, 'resetTwoFactor'])->name('users.reset-two-factor');

        // Visit requests (public books them; admin manages them)
        Route::get('/visit-requests', [VisitRequestController::class, 'index'])->name('visit-requests.index');
        Route::get('/visit-requests/{visitRequest}', [VisitRequestController::class, 'show'])->name('visit-requests.show');
        Route::put('/visit-requests/{visitRequest}/status', [VisitRequestController::class, 'updateStatus'])->name('visit-requests.update-status');
        Route::post('/visit-requests/{visitRequest}/collect-fee', [VisitRequestController::class, 'collectFee'])->name('visit-requests.collect-fee');
    });
});
