<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    PromoController,
    DestinationController,
    StationController,
    TrainController,
    InfoController,
    LoyaltyController,
    TrainManagementController,
    ScheduleController,
    RefundController
};
use App\Http\Controllers\BookingController;

Route::prefix('v1')->middleware('api')->group(function () {
    // ========== Health & Info Endpoints ==========
    Route::get('/health', fn() => response()->json(['status' => 'ok']));
    Route::get('/info', [InfoController::class, 'show']);

    // ========== Authentication Endpoints (Public) ==========
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ========== Authenticated Endpoints ==========
    Route::middleware('auth:sanctum')->group(function () {
        // Bookings/Tickets
        Route::prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
        });
        // Auth endpoints
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // Loyalty endpoints
        Route::prefix('loyalty')->group(function () {
            Route::get('/', [LoyaltyController::class, 'index']);
            Route::post('/claim-reward', [LoyaltyController::class, 'claimReward']);
        });

        // Admin only routes
        Route::middleware('role:admin')->group(function () {
            // Add admin-only endpoints here later
        });
    });

    // ========== Promo Management ==========
    Route::prefix('promos')->group(function () {
        Route::get('/', [PromoController::class, 'index']);
        Route::get('/{id}', [PromoController::class, 'show']);
        Route::post('/search', [PromoController::class, 'search']);
    });

    // ========== Destination Management ==========
    Route::prefix('destinations')->group(function () {
        Route::get('/', [DestinationController::class, 'index']);
        Route::get('/{id}', [DestinationController::class, 'show']);
        Route::post('/search', [DestinationController::class, 'search']);
    });

    // ========== Station Management ==========
    Route::prefix('stations')->group(function () {
        Route::get('/', [StationController::class, 'index']);
        Route::get('/{code}', [StationController::class, 'show']);
        Route::get('/city/{city}', [StationController::class, 'getByCity']);
        Route::post('/search', [StationController::class, 'search']);
    });

    // ========== Train & Booking Management ==========
    Route::prefix('trains')->group(function () {
        Route::get('/', [TrainController::class, 'index']);
        Route::get('/{id}', [TrainController::class, 'show']);
        Route::post('/search', [TrainController::class, 'search']);
        Route::get('/class/{class}', [TrainController::class, 'getByClass']);
        Route::post('/{id}/calculate-price', [TrainController::class, 'calculatePrice']);
    });
});

// ========== API Error Handling ==========
Route::fallback(function (Request $request) {
    return response()->json([
        'error' => 'Not Found',
        'message' => 'The requested endpoint does not exist.',
        'path' => $request->path(),
        'method' => $request->method(),
        'documentation' => 'https://swiftrail-api.local/docs'
    ], 404);
});
