<?php

namespace Devrahul\Signinupapi;

use Illuminate\Support\ServiceProvider;

Class SigninupapiServiceProvider extends ServiceProvider
{

    public function boot()
    {
        
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadViewsFrom(__DIR__.'/views','signinupapi');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        //Publishing Migrations
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations')
        ], 'migrations');

        // Publishing UsersController.php
        $this->publishes([
            __DIR__.'/Http/Controllers/API/v1/UsersController.php' => app_path('/Http/Controllers/API/v1/UsersController.php'),
        ],'UsersController');
     
        // Publishing Role.php File
        $this->publishes([
            __DIR__.'/Models/Role.php' => app_path('/Role.php'),
        ],'Role');
    }

    public function register()
    {

    }

}