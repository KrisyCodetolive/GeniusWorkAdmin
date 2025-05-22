<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\Paie\BulletinPaieRepositoryInterface;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Repositories\Paie\BulletinPaieRepository;
use App\Services\Paie\CalculPaieService;

class PaieServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BulletinPaieRepositoryInterface::class, BulletinPaieRepository::class);
        $this->app->bind(CalculPaieServiceInterface::class, CalculPaieService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
