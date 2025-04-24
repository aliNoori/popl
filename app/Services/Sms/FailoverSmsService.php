<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;

class FailoverSmsService implements SmsServiceInterface
{
    protected array $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function send(string $phone, string $message): bool
    {
        foreach ($this->services as $service) {
            try {
                if ($service->send($phone, $message)) {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning('Failover SMS Service Failed: ' . get_class($service) . ' - ' . $e->getMessage());
            }
        }

        return false;
    }
}
