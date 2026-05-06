<?php

namespace App\Providers;

use App\Contracts\PasarelaPagoContract;
use App\Services\WompiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        |----------------------------------------------------------------------
        | Binding de la pasarela de pago
        |----------------------------------------------------------------------
        | Cada vez que el sistema necesite un PasarelaPagoContract,
        | Laravel inyectará WompiService automáticamente.
        |
        | Si en el futuro se cambia de proveedor, solo se cambia esta línea:
        |   WompiService::class  →  BoldService::class
        |
        | Ningún controlador ni webhook necesita modificarse.
        */
        $this->app->bind(PasarelaPagoContract::class, WompiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}