<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Http;

class KavenegarService implements SmsServiceInterface
{
    protected string $apiKey;
    protected string $sender;

    public function __construct()
    {
        $this->apiKey = config('services.kavenegar.key'); // از فایل config/services.php
        $this->sender = config('services.kavenegar.sender', '10004346'); // پیش‌فرض خط فرستنده
    }

    public function send(string $phone, string $message): bool
    {
        $url = "https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json";

        $response = Http::get($url, [
            'receptor' => $phone,
            'message' => $message,
            'sender' => $this->sender,
        ]);

        if ($response->successful()) {
            logger("Kavenegar → Sent to $phone: $message");
            return true;
        }

        logger()->error('Kavenegar Failed', [
            'phone' => $phone,
            'message' => $message,
            'response' => $response->body()
        ]);

        return false;
    }
}
