<?php

namespace App\Providers;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Support\ServiceProvider;
use App\Filament\Pages\CongesStatistiquesPage;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrer les pages personnalisées
        Pages\Dashboard::getWidgets([
            Widgets\AccountWidget::class,
        ]);
    }
}
