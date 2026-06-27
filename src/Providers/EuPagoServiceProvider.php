<?php

namespace CodeTech\EuPago\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EuPagoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setConfigurations();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setPublishableFiles();

        // Load translations from custom path
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'eupago');

        $this->loadRoutes();
    }

    /**
     * Sets the configuration files.
     */
    private function setConfigurations()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/eupago.php', 'eupago'
        );
    }

    /**
     * Loads the package routes.
     */
    private function loadRoutes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->prefix('eupago')
            ->name('eupago.')
            ->group(__DIR__.'/../../routes/web.php');
    }

    /**
     * Sets the publishable files.
     */
    private function setPublishableFiles()
    {
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../../resources/lang' => resource_path('lang/vendor/eupago'),
        ], 'translations');

        $this->publishes([
            __DIR__.'/../../config/eupago.php' => config_path('eupago.php'),
        ], 'config');
    }
}
