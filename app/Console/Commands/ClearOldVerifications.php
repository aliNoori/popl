<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VerificationCode;

class ClearOldVerifications extends Command
{
    protected $signature = 'clear:old-verifications';
    protected $description = 'پاک‌سازی کدهای تأیید قدیمی یا معتبر شده';

    public function handle(): void
    {
        $deleted = VerificationCode::where('status', 'verified')
            ->orWhere('expires_at', '<', now())
            ->delete();

        $this->info("Deleted $deleted old verification codes.");
    }
}
