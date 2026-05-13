<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Models\Profile;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Schema;

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/auth/verify-email/{id}/{hash}', function (Request $request, string $id, string $hash) {
    if (! $request->hasValidSignature()) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 403);
    }

    $user = \App\Models\User::findOrFail($id);

    if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    $user->markEmailAsVerified();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware('signed')->name('api.verification.verify');

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::delete('/auth/account', [AuthController::class, 'deleteAccount']);
    Route::get('/auth/user', [AuthController::class, 'me']);

    Route::delete('/admin/users/{userId}', function (string $userId) {
        $adminEmails = array_filter(array_map('trim', explode(',', (string) env('ADMIN_EMAILS', ''))));
        if (!in_array(auth('api')->user()->email, $adminEmails, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $user = \App\Models\User::findOrFail($userId);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    });
    Route::get('/user-roles', function (Request $request) {
        $role = $request->query('role');
        $userId = (string) $request->query('user_id');
        $authUser = auth('api')->user();

        if (!$authUser || (string) $authUser->id !== $userId) {
            return response()->json(['has_role' => false]);
        }

        $adminEmails = array_filter(array_map('trim', explode(',', (string) env('ADMIN_EMAILS', ''))));
        $isAdmin = $role === 'admin' && in_array($authUser->email, $adminEmails, true);

        return response()->json(['has_role' => $isAdmin]);
    });

    Route::apiResource('listings', ListingController::class);
    Route::apiResource('applications', ApplicationController::class);
    Route::apiResource('reviews', ReviewController::class);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::patch('/messages/mark-read/{applicationId}', [MessageController::class, 'markReadByApplication']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::patch('/messages/{id}', [MessageController::class, 'update']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markRead']);
    Route::get('/profiles', function (Request $request) {
        $userId = $request->query('user_id');

        if ($userId) {
            $profile = Profile::where('user_id', $userId)->first();
            return response()->json($profile);
        }

        return response()->json(Profile::with('user')->latest()->get());
    });
    Route::patch('/profiles/{id}', function (Request $request, string $id) {
        $profile = Profile::where('user_id', $id)->first();
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        if ((string) auth('api')->id() !== (string) $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|string|max:2048',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:100',
            'last_name_change' => 'nullable|date',
        ]);

        $profile->update($data);
        return response()->json($profile);
    });
    Route::delete('/profiles/{id}', function (string $id) {
        $profile = Profile::where('user_id', $id)->first();
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        if ((string) auth('api')->id() !== (string) $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $profile->delete();
        return response()->json(['message' => 'Profile deleted']);
    });
    Route::get('/contact-messages', function () {
        return response()->json(ContactMessage::latest()->get());
    });
    Route::patch('/contact-messages/{id}', function (Request $request, string $id) {
        $contactMessage = ContactMessage::find($id);
        if (!$contactMessage) {
            return response()->json(['message' => 'Contact message not found'], 404);
        }

        $data = $request->validate([
            'is_read' => 'nullable|boolean',
        ]);

        if (array_key_exists('is_read', $data) && Schema::hasColumn('contact_messages', 'is_read')) {
            $contactMessage->is_read = $data['is_read'];
            $contactMessage->save();
        }

        return response()->json($contactMessage);
    });
    Route::delete('/contact-messages/{id}', function (string $id) {
        $contactMessage = ContactMessage::find($id);
        if (!$contactMessage) {
            return response()->json(['message' => 'Contact message not found'], 404);
        }

        $contactMessage->delete();
        return response()->json(['message' => 'Contact message deleted']);
    });
});

Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);
Route::get('/applications/completed-count', [ApplicationController::class, 'completedCount']);
Route::get('/reviews/listing/{id}', [ReviewController::class, 'forListing']);
Route::post('/contact', [ContactController::class, 'store']);
Route::post('/contact-messages', [ContactController::class, 'store']);