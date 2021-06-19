<?php

namespace EuPago\Providers;

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

        $this->setProviders();
    }

    /**
     * Bootstrap services.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->setPublishableFiles();

        // Load translations from custom path
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'eupago');
    }

    /**
     * Sets the configuration files.
     */
    private function setConfigurations()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/eupago.php', 'eupago'
        );
    }

    /**
     * Sets the custom providers.
     */
    private function setProviders()
    {
        $this->app->register('EuPago\Providers\RouteServiceProvider');
    }

    /**
     * Sets the publishable files.
     */
    private function setPublishableFiles()
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/eupago'),
        ], 'translations');

        $this->publishes([
            __DIR__ . '/../../config/eupago.php' => config_path('eupago.php'),
        ], 'config');
    }
}
