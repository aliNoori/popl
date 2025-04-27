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
        $url = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';

        // Payload for the API request
        $payload = [
            'code' => env('SMS_PATTERN_CODE'),   // Pattern code
            'sender' => env('SMS_SENDER'),       // Sender number
            'recipient' => $phone,              // Destination phone number
            'variable' => [                     // Variables for pattern
                'verification-code' => $message // Dynamic data within pattern
            ]
        ];

        try {
            // Send POST request to SMS service with Basic Auth
            $response = Http::withBasicAuth(env('SMS_USERNAME'), env('SMS_PASSWORD'))
                ->asJson()
                ->post($url, $payload);

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
