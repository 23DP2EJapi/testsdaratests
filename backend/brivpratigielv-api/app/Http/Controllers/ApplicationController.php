<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Listing;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * Return completed jobs count for a volunteer.
     */
    public function completedCount(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $count = Application::where('user_id', $data['user_id'])
            ->where('status', 'approved')
            ->whereHas('listing', function ($query) {
                $query->where('is_completed', true);
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Application::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('listing_id')) {
            $query->where('listing_id', $request->input('listing_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->with(['listing', 'user', 'messages'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'listing_id' => 'required|string',
            'user_id' => 'nullable|integer',
            'full_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'motivation' => 'required|string',
            'cv_url' => 'nullable|string',
        ]);

        $data['status'] = 'pending';

        if (!isset($data['user_id']) && auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        $application = Application::create($data);
        return response()->json($application->load(['listing', 'user']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $application = Application::findOrFail($id);
        return response()->json($application->load(['listing', 'user', 'messages']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $application = Application::findOrFail($id);

        $data = $request->validate([
            'status' => 'nullable|string',
            'phone' => 'nullable|string',
            'motivation' => 'nullable|string',
        ]);

        $application->update($data);
        return response()->json($application->load(['listing', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $application = Application::findOrFail($id);
        $application->delete();

        return response()->json(['message' => 'Application deleted']);
    }
}
