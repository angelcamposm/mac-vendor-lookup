<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Illuminate\Console\Command;

class InstallPackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install MAC Vendor Lookup package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (file_exists(config_path('ieee.php'))) {
            $this->hidden = true;
        }

        $this->info('Installing MAC Vendor Lookup Package...');

        $this->info('Publishing configuration...');

        $this->call('vendor:publish', [
            '--provider' => 'Acamposm\MacVendorLookup\Providers\MacVendorLookupServiceProvider',
            '--tag'      => 'config',
        ]);

        $this->info('Publishing migrations...');

        $this->call('vendor:publish', [
            '--provider' => 'Acamposm\MacVendorLookup\Providers\MacVendorLookupServiceProvider',
            '--tag'      => 'migrations',
        ]);

        $this->info('Installed PingPackage');

        return 0;
    }
}
