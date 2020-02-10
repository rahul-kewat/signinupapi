<?php

namespace Devrahul\Signinupapi;

use Illuminate\Support\ServiceProvider;

Class SigninupapiServiceProvider extends ServiceProvider
{

    public function boot()
    {
        
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/views','signinupapi');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations')
        ], 'migrations');
    }

    public function register()
    {

    }

}