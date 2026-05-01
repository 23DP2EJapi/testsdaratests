<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'full_name' => 'required|string|min:2',
        ]);

        $user = User::create([
            'name'     => $data['full_name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        event(new Registered($user));

        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'user_id')) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $data['full_name']]
            );
        }

        $token = auth('api')->login($user);
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        return response()->json(['token' => $token, 'user' => auth('api')->user()]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function me()
    {
        return response()->json(auth('api')->user()?->load('profile'));
    }
}
