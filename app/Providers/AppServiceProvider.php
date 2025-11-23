<?php

namespace App\Providers;

use App\Services\NotificacionService;
use App\Services\PagoService;
use App\Services\ReservaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar Services
        $this->app->bind(ReservaService::class, function ($app) {
            return new ReservaService;
        });

        $this->app->bind(PagoService::class, function ($app) {
            return new PagoService;
        });

        $this->app->bind(NotificacionService::class, function ($app) {
            return new NotificacionService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
