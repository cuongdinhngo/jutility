<?php

namespace Cuongnd88\JPostalCode;

use Illuminate\Support\ServiceProvider;

class JutilityServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config' => $this->app->configPath(),
        ], 'jpostal-config');

        $this->publishes([
            __DIR__.'/../public' => public_path(),
        ], 'jpostal-public');

        $this->publishes([
            __DIR__.'/../lang' => resource_path(),
        ], 'ja-lang');
    }
}