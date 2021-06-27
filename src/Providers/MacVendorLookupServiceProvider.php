<?php

namespace Acamposm\MacVendorLookup\Providers;

use Acamposm\MacVendorLookup\Console\Commands\InstallPackageCommand;
use Acamposm\MacVendorLookup\OuiFileProcessor;
use Illuminate\Support\ServiceProvider;

class MacVendorLookupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ping');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ieee.php' => config_path('ieee.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([
                InstallPackageCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/ieee.php', 'ieee');

        // Register the main class to use with the facade
        $this->app->singleton('OuiFileProcessor', function () {
            return new OuiFileProcessor();
        });
    }
}
