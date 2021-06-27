<?php

namespace Acamposm\MacVendorLookup\Providers;

use Acamposm\MacVendorLookup\Console\Commands\DownloadOuiFileFromIeeeWebPage;
use Acamposm\MacVendorLookup\Console\Commands\GetMacAddressDetails;
use Acamposm\MacVendorLookup\Console\Commands\InstallPackageCommand;
use Acamposm\MacVendorLookup\Console\Commands\SeedTableFromOuiFile;
use Illuminate\Support\ServiceProvider;

class MacVendorLookupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();

            $this->publishMigrations();

            $this->registerCommands();
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Automatically apply the package configuration.
     */
    private function mergeConfig()
    {
        $path = $this->getConfigPath();

        $this->mergeConfigFrom($path, 'ieee');
    }

    /**
     * Publish Config File.
     */
    private function publishConfig()
    {
        $path = $this->getConfigPath();

        $this->publishes([
            $path => config_path('ieee.php'),
        ], 'config');
    }

    /**
     * Publish Migrations.
     */
    private function publishMigrations()
    {
        $path = $this->getMigrationsPath();

        $this->publishes([
            $path => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Registering package commands.
     */
    private function registerCommands()
    {
        if (!file_exists(config_path('ieee.php'))) {
            $this->commands([
                InstallPackageCommand::class,
            ]);
        }
        $this->commands([
            DownloadOuiFileFromIeeeWebPage::class,
            GetMacAddressDetails::class,
            SeedTableFromOuiFile::class,
        ]);
    }

    private function getConfigPath(): string
    {
        return __DIR__.'/../../config/ieee.php';
    }

    private function getMigrationsPath(): string
    {
        return __DIR__.'/../../database/migrations/';
    }
}
