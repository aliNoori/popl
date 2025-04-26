<?php
namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Http;

class ModirPayamakService implements SmsServiceInterface
{
    /**
     * Send SMS using the specified API
     *
     * @param string $phone The recipient's phone number
     * @param string $message The message to send
     * @return bool True if the SMS was sent successfully, false otherwise
     */
    public function send(string $phone, string $message): bool
    {
        // API endpoint
        $url = 'https://api.ippanel.com/v1/messages/send';

        // Payload for the API request
        $payload = [
            'username' => env('SMS_USERNAME'),   // Your SMS service username
            'password' => env('SMS_PASSWORD'),   // Your SMS service password
            'access_key' => env('SMS_ACCESS_KEY'), // Your access key
            'to' => $phone,                      // Destination phone number
            'message' => $message,               // Message to send
            'pattern_code' => env('SMS_PATTERN_CODE') // Pattern code (if any)
        ];

        try {
            // Send POST request to SMS service
            $response = Http::post($url, $payload);

            // Check if the response indicates success
            if ($response->successful()) {
                logger("SMS sent successfully to $phone: $message");
                return true;
            }

            // Log error if the response is not successful
            logger("Failed to send SMS. Response: " . $response->body());
            return false;
        } catch (\Exception $e) {
            // Log exception if any error occurs
            logger("Exception while sending SMS: " . $e->getMessage());
            return false;
        }
    }
}
