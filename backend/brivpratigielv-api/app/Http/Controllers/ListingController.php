<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Listing::query();

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            } else {
                $query->where('is_active', true);
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            if ($request->has('category') && $request->input('category') !== 'Visi') {
                $query->where('category', $request->input('category'));
            }

            if ($request->has('city') && $request->input('city') !== 'Visas pilsētas') {
                $query->where('location', $request->input('city'));
            }

            if ($request->has('is_online') && $request->input('is_online') === 'true') {
                $query->where('is_online', true);
            }

            if ($request->has('is_urgent') && $request->input('is_urgent') === 'true') {
                $query->where('is_urgent', true);
            }

            if ($request->has('time_commitment') && $request->input('time_commitment') !== 'Visi') {
                $query->where('time_commitment', $request->input('time_commitment'));
            }

            $limit = $request->input('limit', 50);
            $listings = $query->orderBy('created_at', 'desc')->limit($limit)->get();

            return response()->json($listings);
        } catch (\Throwable $e) {
            \Log::error('ListingController@index failed', [
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'request'   => $request->all(),
            ]);

            return response()->json([
                'error'     => 'Failed to fetch listings',
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'category' => 'required|string',
            'time_commitment' => 'nullable|string',
            'spots' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'is_online' => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_active'] = true;
        $data['is_new'] = true;

        $listing = Listing::create($data);
        return response()->json($listing, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $listing = Listing::findOrFail($id);
        return response()->json($listing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $listing = Listing::findOrFail($id);

        if ((string) auth()->id() !== (string) $listing->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'category' => 'nullable|string',
            'time_commitment' => 'nullable|string',
            'spots' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'is_online' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_completed' => 'nullable|boolean',
        ]);

        $listing->update($data);
        return response()->json($listing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $listing = Listing::findOrFail($id);

        if ((string) auth()->id() !== (string) $listing->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $listing->delete();

        return response()->json(['message' => 'Listing deleted']);
    }
}
