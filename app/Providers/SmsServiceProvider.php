<?php

namespace App\Providers;

use App\Contracts\SmsServiceInterface;
use App\Services\Sms\FailoverSmsService;
use App\Services\Sms\KavenegarService;
use App\Services\Sms\ModirPayamakService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SmsServiceInterface::class, function () {
            //priority services
            $services = [
                new KavenegarService(),
                new ModirPayamakService(),
            ];

            return new FailoverSmsService($services);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
