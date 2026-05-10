<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::query();

        if ($request->has('listing_id')) {
            $query->where('listing_id', $request->input('listing_id'));
        }

        if ($request->has('reviewed_user_id')) {
            $query->where('reviewed_user_id', $request->input('reviewed_user_id'));
        }

        if ($request->has('review_type')) {
            $query->where('review_type', $request->input('review_type'));
        }

        $reviews = $query->with(['user', 'listing', 'reviewedUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'listing_id' => 'nullable|string',
            'reviewed_user_id' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'review_type' => 'nullable|string|in:listing,volunteer',
        ]);

        $data['user_id'] = auth()->id();
        $data['review_type'] = $data['review_type'] ?? 'listing';

        $review = Review::create($data);
        return response()->json($review->load(['user', 'listing', 'reviewedUser']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Review::findOrFail($id);
        return response()->json($review->load(['user', 'listing', 'reviewedUser']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        $data = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($data);
        return response()->json($review->load(['user', 'listing', 'reviewedUser']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }

    /**
     * Get reviews for a specific listing.
     */
    public function forListing(string $listingId)
    {
        $reviews = Review::where('listing_id', $listingId)
            ->with(['user', 'reviewedUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }
}
