<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Login information is incorrect.'],
            ]);
        }

        // Optional: Check if user is active
        if ($user->status === 'inactive') {
             return response()->json(['message' => 'Account is locked.'], 403);
        }

        // Create token
        $token = $user->createToken($request->device_name ?? 'web_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('student'),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }
}
