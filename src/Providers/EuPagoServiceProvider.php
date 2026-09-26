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
    private function setConfigurations(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/eupago.php', 'eupago'
        );
    }

    /**
     * Loads the package routes.
     */
    private function loadRoutes(): void
    {
        if (! $this->app['config']->get('eupago.routes')) {
            return;
        }

        if ($this->app->routesAreCached()) {
            return;
        }

        // No middleware: a webhook has no use for a session, and the web group
        // would start one for every call, storing its URL — API key included.
        Route::prefix('eupago')
            ->name('eupago.')
            ->group(__DIR__.'/../../routes/web.php');
    }

    /**
     * Sets the publishable files.
     */
    private function setPublishableFiles(): void
    {
        // The unprefixed tags are kept for backward compatibility; the docs use the prefixed ones.
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], ['eupago-migrations', 'migrations']);

        // Publishing into resources/lang would create that directory, and Laravel
        // then uses it as the app's lang path instead of lang/, hiding the app's
        // own translations.
        $this->publishes([
            __DIR__.'/../../resources/lang' => $this->app->langPath('vendor/eupago'),
        ], ['eupago-translations', 'translations']);

        $this->publishes([
            __DIR__.'/../../config/eupago.php' => config_path('eupago.php'),
        ], ['eupago-config', 'config']);
    }
}
