<?php

namespace Reprover\LaravelYar;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

use Illuminate\Support\Facades\Route;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/../config/yar.php" => config_path('yar.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__ . "/../config/yar-map.php", config_path('yar-map.php'));
        $this->mergeConfigFrom(
            __DIR__ . "/../config/yar-services.php", config_path('yar-services.php'));
        $this->registerRoutes();
    }

    public function registerRoutes()
    {
        $options = [
            'namespace' => 'Reprover\LaravelYar\Controllers',
        ];
        Route::group($options, function () {
            Route::any('/yar/{module}', 'YarController@load');
        });
    }
}
