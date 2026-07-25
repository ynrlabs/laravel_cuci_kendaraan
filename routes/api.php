<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('register', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'register']);
        Route::post('login', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login']);
        Route::post('forgot-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'resetPassword']);
        Route::post('send-otp', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'verifyOtp']);
        Route::post('social-login', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'socialLogin']);
        
        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);
            Route::get('me', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me']);
            Route::put('me', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'updateProfile']);
        });
    });

    // Branches
    Route::prefix('branches')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Branch\BranchController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\V1\Branch\BranchController::class, 'show']);
        Route::get('{id}/services', [\App\Http\Controllers\Api\V1\Branch\BranchController::class, 'services']);
        Route::get('{id}/slots', [\App\Http\Controllers\Api\V1\Branch\BranchController::class, 'slots']);
    });

    // Services
    Route::prefix('services')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Service\ServiceController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\V1\Service\ServiceController::class, 'show']);
    });

    // Bookings (requires auth)
    Route::middleware('auth:sanctum')->prefix('bookings')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'store']);
        Route::get('/', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'show']);
        Route::put('{id}/cancel', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'cancel']);
        Route::post('{id}/review', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'review']);
    });

    // Public booking tracking (by booking code)
    Route::get('bookings/{code}/track', [\App\Http\Controllers\Api\V1\Booking\BookingController::class, 'track']);

    // Payments (requires auth)
    Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\V1\Payment\PaymentController::class, 'store']);
        Route::get('{id}/status', [\App\Http\Controllers\Api\V1\Payment\PaymentController::class, 'status']);
        Route::post('{id}/retry', [\App\Http\Controllers\Api\V1\Payment\PaymentController::class, 'retry']);
    });

    // Payment webhook (public, no auth)
    Route::post('payments/callback', [\App\Http\Controllers\Api\V1\Payment\PaymentController::class, 'callback']);

    // Promos
    Route::prefix('promos')->group(function () {
        Route::get('available', [\App\Http\Controllers\Api\V1\Promo\PromoController::class, 'available']);
        Route::post('apply', [\App\Http\Controllers\Api\V1\Promo\PromoController::class, 'apply']);
    });

    // Notifications (requires auth)
    Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Notification\NotificationController::class, 'index']);
        Route::put('{id}/read', [\App\Http\Controllers\Api\V1\Notification\NotificationController::class, 'markAsRead']);
        Route::put('read-all', [\App\Http\Controllers\Api\V1\Notification\NotificationController::class, 'markAllAsRead']);
        Route::post('fcm-token', [\App\Http\Controllers\Api\V1\Notification\NotificationController::class, 'updateFcmToken']);
    });
});

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');