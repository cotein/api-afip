<?php

namespace Cotein\ApiAfip;

use Illuminate\Support\ServiceProvider;

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

    public function register(): void
    {
        //en éste método registro los binds
        $this->app->bind('Afip', function () {
            return new Afip();
        });
    }
}
