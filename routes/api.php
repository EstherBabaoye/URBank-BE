<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ContactController;

Route::get('/test', function () {
    return "Hello";
});

// ✅ USER AUTH & INTERNET BANKING
Route::post('/register', [UserController::class, 'register']);
Route::post('/register-internet-banking', [UserController::class, 'registerInternetBanking']);
Route::post('/login-internet-banking', [UserController::class, 'loginInternetBanking']);
Route::post('/resend-verification', [UserController::class, 'resendVerification']);
Route::post('/forgot-pin', [UserController::class, 'forgotInternetBankingPin']);
Route::post('/reset-pin', [UserController::class, 'resetInternetBankingPin']);
Route::get('/verify-email', [UserController::class, 'verifyEmail']);

//USER
Route::middleware('auth:user')->group(function () {
    Route::get('/internetbanking/profile/{email}', [UserController::class, 'getProfile']);
});



// ✅ CONTACT
Route::post('/contact', [ContactController::class, 'handleContactSubmission']);

// ✅ ADMIN AUTH (JWT)
Route::post('/admin/login', [AdminController::class, 'adminLogin']);
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminController::class, 'adminLogout']);
    Route::get('/admin/me', [AdminController::class, 'me']);

    // ✅ ADMIN ACCOUNT ACTIONS
    Route::get('/admin/accounts/pending', [AdminController::class, 'getPendingAccounts']);
    Route::post('/admin/accounts/{id}/approve', [AdminController::class, 'approveAccount']);
    Route::post('/admin/accounts/{id}/reject', [AdminController::class, 'rejectAccount']);

    // ✅ CARD OPERATIONS
    Route::get('/admin/cards/pending', [AdminController::class, 'getPendingCards']);
    Route::post('/admin/cards/{id}/approve', [AdminController::class, 'approveCard']);
    Route::post('/admin/cards/{id}/reject', [AdminController::class, 'rejectCard']);
});

// ✅ PUBLIC CARD ROUTE
Route::post('/card/apply', [CardController::class, 'submitApplication']);
