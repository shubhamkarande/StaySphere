<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class BookingController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:listings,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::findOrFail($request->listing_id);

        // Check if listing is available
        if (!$listing->isAvailable($request->check_in, $request->check_out)) {
            return response()->json([
                'message' => 'Listing is not available for selected dates'
            ], 422);
        }

        // Check guest capacity
        if ($request->guests > $listing->accommodates) {
            return response()->json([
                'message' => 'Number of guests exceeds listing capacity'
            ], 422);
        }

        // Calculate pricing
        $pricing = $listing->calculateTotalPrice($request->check_in, $request->check_out, $request->guests);

        // Create booking
        $booking = Booking::create([
            'listing_id' => $listing->id,
            'guest_id' => $request->user()->id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'guests' => $request->guests,
            'nights' => $pricing['nights'],
            'subtotal' => $pricing['subtotal'],
            'cleaning_fee' => $pricing['cleaning_fee'],
            'service_fee' => $pricing['service_fee'],
            'total_amount' => $pricing['total'],
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_STATUS_PENDING,
            'special_requests' => $request->special_requests,
        ]);

        // Create Stripe checkout session
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $listing->title,
                            'description' => "Stay from {$request->check_in} to {$request->check_out}",
                        ],
                        'unit_amount' => $pricing['total'] * 100, // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => config('app.frontend_url') . '/booking/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/booking/cancel',
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

            $booking->update(['payment_intent_id' => $session->id]);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load(['listing', 'guest']),
                'checkout_url' => $session->url,
            ], 201);

        } catch (\Exception $e) {
            $booking->delete();
            return response()->json([
                'message' => 'Failed to create payment session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $session = Session::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                $booking = Booking::where('payment_intent_id', $session->id)->firstOrFail();
                $booking->confirm();

                return response()->json([
                    'message' => 'Payment confirmed successfully',
                    'booking' => $booking->load(['listing', 'guest'])
                ]);
            }

            return response()->json([
                'message' => 'Payment not completed'
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $bookings = Booking::where('guest_id', $request->user()->id)
            ->with(['listing.host'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::where('guest_id', $request->user()->id)
            ->with(['listing.host', 'review'])
            ->findOrFail($id);

        return response()->json([
            'data' => $booking
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('guest_id', $request->user()->id)
            ->findOrFail($id);

        if (!$booking->can_cancel) {
            return response()->json([
                'message' => 'Booking cannot be cancelled'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->cancel($request->reason);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking->fresh()
        ]);
    }

    public function hostBookings(Request $request)
    {
        $bookings = Booking::whereHas('listing', function ($query) use ($request) {
                $query->where('host_id', $request->user()->id);
            })
            ->with(['listing', 'guest'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ]
        ]);
    }
}