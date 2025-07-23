<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(Request $request, $listingId = null)
    {
        $query = Review::with(['reviewer', 'listing']);

        if ($listingId) {
            $query->where('listing_id', $listingId);
        }

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify booking belongs to user and is completed
        $booking = Booking::where('guest_id', $request->user()->id)
            ->where('id', $request->booking_id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->firstOrFail();

        // Check if review already exists
        if ($booking->review()->exists()) {
            return response()->json([
                'message' => 'Review already exists for this booking'
            ], 422);
        }

        $review = Review::create([
            'listing_id' => $booking->listing_id,
            'booking_id' => $booking->id,
            'reviewer_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review->load(['reviewer', 'listing'])
        ], 201);
    }

    public function myReviews(Request $request)
    {
        $reviews = Review::where('reviewer_id', $request->user()->id)
            ->with(['listing', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    public function receivedReviews(Request $request)
    {
        $reviews = Review::whereHas('listing', function ($query) use ($request) {
                $query->where('host_id', $request->user()->id);
            })
            ->with(['reviewer', 'listing', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }
}