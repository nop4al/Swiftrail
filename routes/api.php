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
    RefundController,
    TrainScheduleController,
    BookingController as ApiBookingController,
    PaymentController,
    TrackingLiveController,
    TrainTrackingController,
    SwiftPayController,
    ProfilePhotoController
};
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MidtransController;

Route::prefix('v1')->middleware('api')->group(function () {
    // ========== Health & Info Endpoints ==========
    Route::get('/health', fn() => response()->json(['status' => 'ok']));
    Route::get('/info', [InfoController::class, 'show']);

    // ========== Authentication Endpoints (Public) ==========
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ========== Live Tracking (Public) ==========
    Route::prefix('tracking')->group(function () {
        Route::get('/{train_code}', [TrainTrackingController::class, 'show']);
    });

    // ========== Authenticated Endpoints ==========
    Route::middleware('auth:sanctum')->group(function () {
                // Profile photo upload/delete
                Route::post('/profile/photo', [ProfilePhotoController::class, 'uploadPhoto']);
                Route::delete('/profile/photo', [ProfilePhotoController::class, 'deletePhoto']);
        // Bookings/Tickets
        Route::prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::get('/{id}', [BookingController::class, 'show']);
            Route::put('/{id}', [BookingController::class, 'update']);
            Route::post('/{bookingCode}/confirm-payment', [BookingController::class, 'confirmPayment']);
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

        // ========== SwiftPay Digital Wallet ==========
        Route::prefix('swiftpay')->group(function () {
            // Get wallet info
            Route::get('/wallet', [SwiftPayController::class, 'getWallet']);
            // Get wallet stats
            Route::get('/stats', [SwiftPayController::class, 'getStats']);
            // Get transactions
            Route::get('/transactions', [SwiftPayController::class, 'getTransactions']);
            // Top up balance
            Route::post('/topup', [SwiftPayController::class, 'topup']);
            // Make payment
            Route::post('/payment', [SwiftPayController::class, 'payment']);
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

    // ========== Train Schedule - Seat Selection Flow ==========
    Route::prefix('schedules')->group(function () {
        // Get all schedules with filtering
        Route::get('/', [TrainScheduleController::class, 'getSchedules']);
        // Get schedule details
        Route::get('/{scheduleId}', [TrainScheduleController::class, 'getScheduleDetail']);
    });

    // ========== Booking & Seat Management ==========
    Route::prefix('bookings')->group(function () {
        // Get available trains for route and date (must be before {scheduleId} routes)
        Route::post('/trains/available', [ApiBookingController::class, 'getAvailableTrains']);
        // Get available seats for a schedule
        Route::get('/{scheduleId}/seats', [ApiBookingController::class, 'getAvailableSeats']);
        // Create a booking
        Route::post('/', [ApiBookingController::class, 'createBooking']);
        // Get booking details
        Route::get('/{bookingId}', [ApiBookingController::class, 'getBooking']);
        // Cancel booking
        Route::delete('/{bookingId}', [ApiBookingController::class, 'cancelBooking']);
    });

    // ========== Payment & Checkout Flow ==========
    Route::prefix('payments')->group(function () {
        // Get available payment methods
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        // Get order confirmation
        Route::get('/confirmation/{bookingId}', [PaymentController::class, 'getOrderConfirmation']);
        // Process payment
        Route::post('/process', [PaymentController::class, 'processPayment']);
    });

    // ========== Midtrans Payment Gateway ==========
    Route::prefix('midtrans')->group(function () {
        // Midtrans Snap endpoint - creates transaction and returns token
        Route::post('/create', [MidtransController::class, 'create']);
        // Midtrans webhook - records notifications from Midtrans
        Route::post('/webhook', [MidtransController::class, 'webhook']);
        // Midtrans client confirmation - persists Snap client callback
        Route::post('/confirm', [MidtransController::class, 'confirm']);
        // Midtrans callback endpoints (no auth required)
        Route::get('/finish', [MidtransController::class, 'finish']);
        Route::get('/error', [MidtransController::class, 'error']);
        Route::get('/pending', [MidtransController::class, 'pending']);
        // Public: get payment details by order id (used by frontend after redirect)
        Route::get('/payment/{orderId}', [MidtransController::class, 'getPayment']);
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
