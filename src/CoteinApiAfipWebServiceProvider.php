<?php

namespace Cotein\ApiAfip;

use Illuminate\Support\ServiceProvider;
use Cotein\ApiAfip\Afip;

class CoteinApiAfipWebServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // éste método se llama después que los service providers hayan sido registrados
        /* Route::post('api-afip/getPersona', function () {
            return ApiAfip::hello();
        }); */

        $this->loadMigrationsFrom(__DIR__ . './../database/migrations');
        $this->publishes([
            __DIR__ . './../database/migrations' => database_path('migrations')
        ], 'api-afip-migrations');
    }

    public function register()
    {
        $this->app->singleton('afip-web-service', function ($app) {
            return new Afip();
        });
    }
}
