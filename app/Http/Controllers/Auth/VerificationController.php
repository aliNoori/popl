<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\SmsServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * Send verification code via SMS
     *
     * @throws \Exception
     */
    public function send(Request $request, SmsServiceInterface $sms): JsonResponse
    {
        // Validate phone number
        $request->validate([
            'phone' => 'required|string',
            'country' => 'required|string',
        ]);

        //$fullPhone = $request->country . ltrim($request->phone, '0');
        //$fullPhone = '0' . ltrim($request->phone, '0');
        $fullPhone=$request->country.$request->phone;
        $processedPhone = ltrim($fullPhone, '+');
        // Check if a code was recently sent (within the last 2 minutes)
        $recent = VerificationCode::where('phone', $processedPhone)
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();

        if ($recent) {
            return response()->json(['message' => 'کد قبلی هنوز معتبر است'], 429);
        }

        // Generate a secure 6-digit random code
        $code = random_int(1000, 9999);

        // Store the verification code in the database
        VerificationCode::create([
            'phone' => $processedPhone,
            'code' => $code,
            'expires_at' => now()->addMinutes(2), // Code expires after 2 minutes
        ]);
        // Send the verification code via SMS
        $sms->send($fullPhone, "{$code}");

        return response()->json(['message' => 'کد ارسال شد']);
    }

    /**
     * Check submitted verification code
     */
    public function check(Request $request): JsonResponse
    {
        // Validate request input
        $data = $request->validate([
            'country' => 'required',
            'phone' => 'required',
            'code' => 'required'
        ]);

        // Normalize phone number for Iran
        /*if ($data['country'] === 'IR') {
            $data['phone'] = preg_replace('/^\+98/', '0', $data['phone']);
        }*/
        $processedPhone = ltrim($data['phone'], '+');
        Log::info('wee',[$processedPhone,$data['code']]);
        // Find a matching, non-expired, pending verification record
        $record = VerificationCode::where('phone', $processedPhone)
            ->where('code', $data['code'])
            ->where('expires_at', '>', now())
            ->where('status', 'pending')
            ->first();

        // If no valid code is found, return error
        if (!$record) {
            return response()->json(['message' => 'کد نامعتبر یا منقضی شده است'], 422);
        }

        // Mark the code as verified
        $record->update(['status' => 'verified']);

        // Check if the user exists by phone number
        $user = User::where('phone', $processedPhone)->first();

        // If the user already exists, return a conflict status
        if ($user) {
            // Generate a Sanctum token for the new user
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                'message' => 'کاربر از قبل وجود دارد',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        // Create a new user if not found
        $user = User::create([
            'phone' => $processedPhone,
            'name' => 'User ' . $processedPhone,
            'email' => $data['phone'] . '@example.com',
            'password' => Hash::make('default_password'),
        ]);

        // Generate a Sanctum token for the new user
        $token = $user->createToken('authToken')->plainTextToken;

        // Return a success response with the new user and token
        return response()->json([
            'message' => 'کاربر جدید با موفقیت ایجاد شد',
            'token' => $token,
            'user' => $user,
        ], 201);
    }
}
