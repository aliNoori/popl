<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Update the user's profile (e.g. name and referral code)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // Validate the request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'referral_code' => 'nullable|string|max:10|unique:users,referral_code,' . $request->user()->id,
        ]);

        // Update current authenticated user
        $user = $request->user();
        $user->update([
            'name' => $data['name'],
            'referral_code' => $data['referral_code'] ?? null,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user
        ]);
    }

    /**
     * Log the user in using phone number and verification code
     */
    public function login(Request $request): JsonResponse
    {
        // Validate login credentials
        $credentials = $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string', // Replace with 'password' if using password authentication
        ]);

        // Try to find the user by phone
        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Here, you should verify the code (this part depends on your SMS verification implementation)

        // Log the user in
        Auth::login($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }

    /**
     * Log the user out and invalidate the session
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout successful']);
    }

    /**
     * Get the currently authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
