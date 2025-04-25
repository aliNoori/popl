<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\SmsServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $fullPhone = '0' . ltrim($request->phone, '0');
        // Check if a code was recently sent (within the last 2 minutes)
        $recent = VerificationCode::where('phone',$fullPhone )
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();

        if ($recent) {
            return response()->json(['message' => 'کد قبلی هنوز معتبر است'], 429);
        }

        // Generate a secure 6-digit random code
        $code = random_int(1000, 9999);

        // Store the verification code in the database
        VerificationCode::create([
            'phone' => $fullPhone,
            'code' => $code,
            'expires_at' => now()->addMinutes(2), // Code expires after 2 minutes
        ]);
        // Send the verification code via SMS
        $sms->send($fullPhone, "کد تایید شما: {$code}");

        return response()->json(['message' => 'کد ارسال شد']);
    }

    /**
     * Check submitted verification code
     */
    public function check(Request $request): JsonResponse
    {
        // Validate request input
        $data = $request->validate([
            'country'=>'required',
            'phone' => 'required',
            'code' => 'required'
        ]);
        if ($data['country'] === 'IR') {

            $data['phone'] = preg_replace('/^\+98/', '0', $data['phone']);
        }


        // Find a matching, non-expired, pending verification record
        $record = VerificationCode::where('phone', $data['phone'])
            ->where('code', $data['code'])
            ->where('expires_at', '>', now())
            ->where('status', 'pending')
            ->first();

        // If no valid code is found, return error
        if (!$record) {
            return response()->json(['message' => 'Invalid or expired verification code'], 422);
        }

        // Mark the code as verified
        $record->update(['status' => 'verified']);

        // You can authenticate the user here and return a token (e.g. JWT)
        // Find or create user by phone number
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => 'User ' . $request->phone,
                'email' => $request->phone . '@example.com',
                'password' => Hash::make('default_password'),
            ]
        );

        // Create Sanctum token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Verification successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
