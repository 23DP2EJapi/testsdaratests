<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Application;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Message::query();

        if ($request->has('application_id')) {
            $query->where('application_id', $request->input('application_id'));
        }

        if ($request->has('sender_id')) {
            $query->where('sender_id', $request->input('sender_id'));
        }

        $messages = $query->with(['application', 'sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|string',
            'content' => 'required|string',
        ]);

        $data['sender_id'] = auth()->id();

        $application = Application::findOrFail($data['application_id']);

        $message = Message::create($data);
        return response()->json($message->load(['application', 'sender']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = Message::findOrFail($id);
        return response()->json($message->load(['application', 'sender']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $message = Message::findOrFail($id);

        $data = $request->validate([
            'content' => 'nullable|string',
        ]);

        $message->update($data);
        return response()->json($message->load(['application', 'sender']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }

    /**
     * Mark a message as read.
     */
    public function markRead(string $id)
    {
        $message = Message::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json($message->load(['application', 'sender']));
    }

    /**
     * Mark all unread messages in an application as read for current user.
     */
    public function markReadByApplication(Request $request, string $applicationId)
    {
        $data = $request->validate([
            'currentUserId' => 'required',
        ]);

        Message::where('application_id', $applicationId)
            ->where('sender_id', '!=', (int) $data['currentUserId'])
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    /**
     * Return unread message count for an application and user.
     */
    public function unreadCount(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|string',
            'current_user_id' => 'required',
        ]);

        $count = Message::where('application_id', $data['application_id'])
            ->where('sender_id', '!=', (int) $data['current_user_id'])
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
