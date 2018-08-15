<?php

namespace Reprover\LaravelYar;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

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
            __DIR__ . "/../config/yar-map.php" => config_path('yar-map.php'),
            __DIR__
            . "/../config/yar-services.php" => config_path('yar-services.php'),
        ]);
        $this->registerRoutes();
    }

    public function register()
    {
        $this->app->singleton('yar', function ($app) {
            return new Yar();
        });

    }

    public function registerRoutes()
    {
        $options = [
            'namespace' => 'Reprover\LaravelYar\Controllers',
            'prefix' => '/yar',
        ];
        Route::group($options, function () {
            Route::any('/{module}', 'YarController@load');
        });
    }
}
