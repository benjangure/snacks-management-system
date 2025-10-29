<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Registration request data:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:255|unique:users|alpha_dash',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:buyer,seller',
        ]);

        // Generate username if not provided
        $username = $request->username;
        if (empty($username)) {
            $baseUsername = strtolower(str_replace(' ', '_', $request->name));
            $username = $baseUsername;
            $counter = 1;
            
            // Ensure username is unique
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }
        }

        \Log::info('Creating user with username:', ['username' => $username]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        \Log::info('Login attempt - All request data:', $request->all());
        \Log::info('Login attempt - Headers:', $request->headers->all());
        
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        // Check if login is email or username
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        \Log::info('Login field detected:', ['field' => $loginField, 'value' => $request->login]);
        
        $user = User::where($loginField, $request->login)->first();

        if (!$user) {
            \Log::info('User not found', ['field' => $loginField, 'value' => $request->login]);
            throw ValidationException::withMessages([
                'login' => ['No account found with these credentials.'],
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            \Log::info('Password mismatch for user:', ['user_id' => $user->id]);
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        \Log::info('Login successful', ['user_id' => $user->id, 'username' => $user->username]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}