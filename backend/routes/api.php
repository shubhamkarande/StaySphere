<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Listings (public)
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);
Route::get('/listings/{id}/reviews', [ReviewController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/become-host', [AuthController::class, 'becomeHost']);

    // Listings (authenticated)
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{id}', [ListingController::class, 'update']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']);
    Route::get('/my-listings', [ListingController::class, 'myListings']);

    // Bookings
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/confirm-payment', [BookingController::class, 'confirmPayment']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/host/bookings', [BookingController::class, 'hostBookings']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    Route::get('/received-reviews', [ReviewController::class, 'receivedReviews']);

    // Messages
    Route::get('/conversations', [MessageController::class, 'getConversations']);
    Route::post('/conversations', [MessageController::class, 'getOrCreateConversation']);
    Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::put('/conversations/{id}/read', [MessageController::class, 'markAsRead']);

    // Uploads
    Route::post('/upload/image', [UploadController::class, 'uploadImage']);
    Route::post('/upload/images', [UploadController::class, 'uploadMultipleImages']);
    Route::delete('/upload/image', [UploadController::class, 'deleteImage']);
});

// Webhook routes (no auth required)
Route::post('/webhooks/stripe', function (Request $request) {
    // Handle Stripe webhooks
    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = config('services.stripe.webhook_secret');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        
        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'];
                // Handle successful payment
                break;
            default:
                // Unexpected event type
                break;
        }

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
});