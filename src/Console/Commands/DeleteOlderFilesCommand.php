<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Illuminate\Console\Command;

class DeleteOlderFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:delete-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes files older than 15 days from storage folder.';

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
        //TODO: Create the logic to perform the deletion on
        // files older than 15 days from storage folder...
        return 0;
    }
}
