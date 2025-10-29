<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MandaziController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SellerPriceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group.
|
*/

// -------------------- Public Routes --------------------
// User authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// M-Pesa Callback (must be public)
Route::post('/mpesa/callback', [PaymentController::class, 'handleCallback']);

// Temporary callback test endpoint
Route::post('/test-callback', function (Illuminate\Http\Request $request) {
    \Log::info('🎯 TEST CALLBACK RECEIVED', $request->all());
    return response()->json(['message' => 'Test callback received successfully']);
});

// Debug route to check users
Route::get('/debug/users', function () {
    $users = \App\Models\User::all(['id', 'name', 'email', 'username', 'role']);
    return response()->json($users);
});

// Public mandazi route for testing
Route::get('/public/mandazi', [MandaziController::class, 'publicIndex']);

// Temporary public sellers route for testing
Route::get('/public/sellers', [MandaziController::class, 'getSellers']);

// Simple test route
Route::get('/test-simple', function () {
    return response()->json(['message' => 'Simple route works']);
});


// -------------------- Protected Routes (Auth Required) --------------------
Route::middleware('auth:sanctum')->group(function () {

    // User actions
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // -------------------- Mandazi Routes --------------------
    Route::get('/mandazi/stats', [MandaziController::class, 'stats']);
    Route::get('/mandazi', [MandaziController::class, 'index']);
    Route::post('/mandazi', [MandaziController::class, 'store']);
    Route::get('/mandazi/{id}', [MandaziController::class, 'show']);
    Route::delete('/mandazi/{id}', [MandaziController::class, 'destroy']);

    // Get all sellers for buyer dropdown
    Route::get('/sellers', [MandaziController::class, 'getSellers']);

    // -------------------- Seller Price Routes --------------------
    Route::get('/seller/price', [SellerPriceController::class, 'index']);
    Route::post('/seller/price', [SellerPriceController::class, 'store']);

    // -------------------- Payment Routes --------------------
    Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);
    Route::post('/mandazi/{id}/pay', [PaymentController::class, 'processPayment']);
    Route::post('/mandazi/{id}/process-payment', [PaymentController::class, 'processPayment']);
    Route::get('/mandazi/{id}/status', [PaymentController::class, 'checkStatus']);

    // -------------------- Admin Routes --------------------
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/orders', [AdminController::class, 'allOrders']);
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::get('/users/{id}/orders', [AdminController::class, 'userOrders']);
        Route::get('/sales-chart', [AdminController::class, 'salesChart']);
    });

});