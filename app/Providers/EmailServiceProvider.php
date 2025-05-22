<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Mail\EmailServiceInterface;
use App\Services\Mail\SmtpEmailService;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EmailServiceInterface::class, function ($app) {
            return new SmtpEmailService();
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
