<?php

namespace App\Providers;

use App\Services\OtpService;
use App\Services\SmsService;
use App\Services\SMS\OrangeSMSService;
use App\Services\SMS\SMSLogService;
use App\Services\WebAuthnService;
use App\Services\PresenceService;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SMSLogService::class);
        
        $this->app->singleton(OrangeSMSService::class, function ($app) {
            return new OrangeSMSService($app->make(SMSLogService::class));
        });
        
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService($app->make(OrangeSMSService::class));
        });
        
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService($app->make(SmsService::class));
        });
        
        // Services pour le pointage mobile
        $this->app->singleton(WebAuthnService::class);
        $this->app->singleton(PresenceService::class);
        $this->app->singleton(QRCodeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
        
        
        // Register Blade components
        Blade::componentNamespace('App\\View\\Components', 'app');
        
        // Register component aliases
        Blade::component('app.components.stat-card', 'app.components.stat-card');
        Blade::component('app.components.ui.alert', 'app.components.alert');
        Blade::component('app.components.ui.button', 'app.components.button');
        Blade::component('app.components.data.card', 'app.components.card');
        
        // Register data components
        Blade::component('app.components.data.card', 'data.card');
        Blade::component('app.components.data.data-table', 'data.data-table');
        Blade::component('app.components.data.department-card', 'data.department-card');
        Blade::component('app.components.data.timeline', 'data.timeline');
        
        // Register UI components
        Blade::component('app.components.ui.tooltip', 'ui.tooltip');
        Blade::component('app.components.ui.progress', 'ui.progress');
        Blade::component('app.components.ui.filter-bar', 'ui.filter-bar');
        Blade::component('app.components.ui.filter-select', 'ui.filter-select');
        Blade::component('app.components.ui.filter-search', 'ui.filter-search');
        Blade::component('app.components.ui.stepper', 'ui.stepper');
        Blade::component('app.components.ui.sidebar', 'ui.sidebar');
        Blade::component('app.components.ui.sidebar-item', 'ui.sidebar-item');
        
        // Register form components
        Blade::component('app.components.form.form-input', 'form.form-input');
        Blade::component('app.components.form.form-select', 'form.form-select');
        Blade::component('app.components.form.date-picker', 'form.date-picker');
        Blade::component('app.components.form.rich-editor', 'form.rich-editor');
        Blade::component('app.components.form.employee-select', 'form.employee-select');
        Blade::component('app.components.form.form-actions', 'form.form-actions');
        
        // Register modal components
        Blade::component('app.components.modal.slide-over', 'modal.slide-over');
        
        // Register biometrique components
        Blade::component('App\View\Components\Biometrique\DeviceForm', 'biometrique.components.forms.device-form');
        Blade::component('App\View\Components\Biometrique\Components\Modals\DeviceSyncModal', 'biometrique.components.modals.device-sync-modal');
        Blade::component('App\View\Components\Biometrique\Components\Modals\DeviceTestModal', 'biometrique.components.modals.device-test-modal');
    }
}
