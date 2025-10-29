<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MandaziController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SellerPriceController;

// Test route
Route::get('/test-simple', function () {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// M-Pesa callback (must be public)
Route::post('/mpesa/callback', [PaymentController::class, 'handleCallback']);

// Test callback endpoint
Route::post('/test/callback/{checkoutRequestId}', [PaymentController::class, 'testCallback']);

// Public sellers
Route::get('/public/sellers', [MandaziController::class, 'getSellers']);

// Debug users
Route::get('/debug/users', function () {
    $users = \App\Models\User::all(['id', 'name', 'email', 'username', 'role']);
    return response()->json($users);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/sellers', [MandaziController::class, 'getSellers']);
    Route::get('/mandazi', [MandaziController::class, 'index']);
    Route::post('/mandazi', [MandaziController::class, 'store']);
    Route::get('/mandazi/stats', [MandaziController::class, 'stats']);
    
    // Seller price management
    Route::get('/seller/price', [SellerPriceController::class, 'index']);
    Route::post('/seller/price', [SellerPriceController::class, 'store']);
    
    // Payment routes
    Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);
    Route::post('/mandazi/{id}/pay', [PaymentController::class, 'processPayment']);
    Route::get('/mandazi/{id}/status', [PaymentController::class, 'checkStatus']);
});