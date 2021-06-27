<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Acamposm\MacVendorLookup\Exceptions\OuiFileNotFoundException;
use Acamposm\MacVendorLookup\OuiFile;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeedTableFromOuiFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:insert {--file= : The file to seed database.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initial seed command from each assignment file published by IEEE.';

    protected OuiFile $file;

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
     * @throws OuiFileNotFoundException
     */
    public function handle()
    {
        $this->file = new OuiFile(
            $this->option('file') ?? '',
            $this->option('file') != null
        );

        self::seedTable(self::processCsv());

        return 0;
    }

    /**
     * Process the recently downloaded oui.csv
     *
     * @return array
     */
    private function processCsv(): array
    {
        $this->info(' Processing the CSV file');

        $csv = array_map('str_getcsv', file(storage_path($this->file->fullPath())));

        array_shift($csv);

        $column_names = ['registry', 'oui', 'organization', 'address', 'created_at'];

        array_walk($csv, function (&$value) use ($column_names) {
            $value = array_combine($column_names, [...$value, Carbon::now()->toDateTimeString()]);
        });

        return $csv;
    }

    /**
     * Seed the table ieee_oui_vendors
     *
     * @param array $csv
     */
    private function seedTable(array $csv): void
    {
        $this->info(' Seeding the table ieee_oui_vendors');

        $chunks = (collect($csv))->chunk(100);

        $this->output->progressStart($chunks->count());

        foreach ($chunks as $chunk) {

            DB::table('ieee_oui_assignments')->insert($chunk->toArray());

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
