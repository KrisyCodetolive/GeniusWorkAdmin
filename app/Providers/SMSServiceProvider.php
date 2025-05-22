<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SMS\SMSServiceInterface;
use App\Services\SMS\WelcomeSMSService;

class SMSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SMSServiceInterface::class, function ($app) {
            return $app->make(WelcomeSMSService::class);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
