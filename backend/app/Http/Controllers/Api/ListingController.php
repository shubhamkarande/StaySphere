<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['host', 'reviews'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('location') && $request->location) {
            $query->byLocation($request->location);
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        if ($request->has('property_type') && $request->property_type) {
            $query->byPropertyType($request->property_type);
        }

        if ($request->has('room_type') && $request->room_type) {
            $query->byRoomType($request->room_type);
        }

        if ($request->has('guests') && $request->guests) {
            $query->byGuests($request->guests);
        }

        if ($request->has('bedrooms') && $request->bedrooms) {
            $query->byBedrooms($request->bedrooms);
        }

        if ($request->has('bathrooms') && $request->bathrooms) {
            $query->byBathrooms($request->bathrooms);
        }

        if ($request->has('amenities') && $request->amenities) {
            $query->byAmenities($request->amenities);
        }

        if ($request->has('instant_book') && $request->instant_book) {
            $query->instantBook();
        }

        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        // Pagination
        $perPage = $request->get('limit', 12);
        $listings = $query->paginate($perPage);

        return response()->json([
            'data' => $listings->items(),
            'meta' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $listing = Listing::with(['host', 'reviews.reviewer', 'availabilities'])
            ->findOrFail($id);

        return response()->json([
            'data' => $listing
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|string|in:apartment,house,villa,cabin,loft,other',
            'room_type' => 'required|string|in:entire_place,private_room,shared_room',
            'accommodates' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|numeric|min:0|max:10',
            'price_per_night' => 'required|numeric|min:1',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'minimum_nights' => 'required|integer|min:1|max:365',
            'maximum_nights' => 'nullable|integer|min:1|max:365',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'amenities' => 'nullable|array',
            'images' => 'required|array|min:1',
            'images.*' => 'url',
            'house_rules' => 'nullable|array',
            'cancellation_policy' => 'required|string|in:flexible,moderate,strict',
            'instant_book' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::create([
            'host_id' => $request->user()->id,
            ...$request->validated()
        ]);

        return response()->json([
            'message' => 'Listing created successfully',
            'data' => $listing->load('host')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::where('host_id', $request->user()->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'property_type' => 'sometimes|string|in:apartment,house,villa,cabin,loft,other',
            'room_type' => 'sometimes|string|in:entire_place,private_room,shared_room',
            'accommodates' => 'sometimes|integer|min:1|max:20',
            'bedrooms' => 'sometimes|integer|min:0|max:10',
            'bathrooms' => 'sometimes|numeric|min:0|max:10',
            'price_per_night' => 'sometimes|numeric|min:1',
            'cleaning_fee' => 'sometimes|nullable|numeric|min:0',
            'service_fee' => 'sometimes|nullable|numeric|min:0',
            'minimum_nights' => 'sometimes|integer|min:1|max:365',
            'maximum_nights' => 'sometimes|nullable|integer|min:1|max:365',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'amenities' => 'sometimes|nullable|array',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'url',
            'house_rules' => 'sometimes|nullable|array',
            'cancellation_policy' => 'sometimes|string|in:flexible,moderate,strict',
            'instant_book' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing->update($request->validated());

        return response()->json([
            'message' => 'Listing updated successfully',
            'data' => $listing->fresh()->load('host')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $listing = Listing::where('host_id', $request->user()->id)
            ->findOrFail($id);

        $listing->delete();

        return response()->json([
            'message' => 'Listing deleted successfully'
        ]);
    }

    public function myListings(Request $request)
    {
        $listings = Listing::where('host_id', $request->user()->id)
            ->with(['reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $listings->items(),
            'meta' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ]
        ]);
    }
}