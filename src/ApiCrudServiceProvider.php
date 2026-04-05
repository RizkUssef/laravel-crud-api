<?php

namespace Rizkussef\LaravelCrudApi;

use Illuminate\Support\ServiceProvider;

class ApiCrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Rizkussef\LaravelCrudApi\Console\Commands\MakeApiCrudCommand::class,
            ]);
        }
        $this->publishes([
            __DIR__ . '/config/api-crud.php' => config_path('api-crud.php'),
        ], 'config');
    }
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/api-crud.php',
            'api-crud'
        );
    }
}
