<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $this->validateOrLog($request, [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ], 'API login');

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            Log::warning('auth.login.failed', [
                'who' => LogContext::who(email: $data['email']),
                'what' => 'API login attempt was rejected',
                'why' => 'the email and password did not match any account',
            ]);

            throw ValidationException::withMessages([
                'email' => ['kredensial login salah.']
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'api-token')->plainTextToken;

        Log::info('auth.login.succeeded', [
            'who' => LogContext::who($user->id),
            'what' => 'API login completed and an access token was issued',
            'why' => 'the credentials matched an existing account',
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            
        ]);
    }

    public function logout(Request $request)
    {

    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Log out sukses']);
    }
}
