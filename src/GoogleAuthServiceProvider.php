<?php

namespace Origami\GoogleAuth;

use Illuminate\Support\ServiceProvider;
use Origami\GoogleAuth\Cache\Pool;

class GoogleAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/google-auth.php' => $this->app->configPath('google-auth.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/google-auth.php',
            'google-auth'
        );

        $this->app->singleton(GoogleAuth::class, function ($app) {
            return new GoogleAuth(
                $app['config']['google-auth'],
                $app->make(Pool::class)
            );
        });
    }
}
