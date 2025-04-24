<?php
namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;

class ModirPayamakService implements SmsServiceInterface
{
    public function send(string $phone, string $message): bool
    {
        // فراخوانی API واقعی Modir
        logger("ModirPayamak → $phone: $message");
        return true;
    }
}
