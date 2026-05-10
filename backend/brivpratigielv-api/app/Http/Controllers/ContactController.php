<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Store a contact form submission.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'subject' => 'required|string|min:5',
            'message' => 'required|string|min:10',
        ]);

        $contactMessage = ContactMessage::create($data);
        return response()->json([
            'message' => 'Contact message sent successfully',
            'data' => $contactMessage,
        ], 201);
    }
}
