<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Acamposm\MacVendorLookup\OuiFile;
use Acamposm\MacVendorLookup\OuiFileProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateTableFromOuiFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:update {--file= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database records from the given file.';

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
     * @throws \Exception
     *
     * @return int
     */
    public function handle()
    {
        $this->file = new OuiFile($this->option('file'), $this->option('file') !== null);

        $previous_file = self::selectPreviousFileDetails();

        if ($this->file->hash() != $previous_file->hash) {

            // Load current CSV
            $current_csv = self::loadFile($this->file->fullPath());

            // Load previous CSV
            $previous_csv = self::loadFile((new OuiFile($previous_file->name, true))->fullPath());

            $current_oids = $current_csv->pluck('oui')->toArray();
            $previous_oids = $previous_csv->pluck('oui')->toArray();

            $additions = self::checkAdditions($current_oids, $previous_oids);

            // TODO: Select from current_csv the oui from the additions array,
            //  then insert in to the database.

            //$deletions = self::checkDeletions($current_oids, $previous_oids);

            // TODO: DELETE from database the oui form the deletions array...

            //unset($current_oids);
            //unset($previous_oids);

            dd([
                'total'   => $current_csv->count(),
                'cleaned' => $current_csv->whereNotIn('oui', $additions)->count(),
            ]);

            $updates =

            $stats = [
                'additions' => count($additions),
                'deletions' => count($deletions),
                'updates'   => 0,
            ];

            dd($stats);
        }

        return 0;
    }

    /**
     * Loads the specified file and returns a collection.
     *
     * @param string $path
     *
     * @return Collection
     */
    private function loadFile(string $path): Collection
    {
        $this->info(' Loading file: '.$path);

        return (new OuiFileProcessor())
            ->load($path)
            ->toCollection();
    }

    /**
     * Check for new additions to the OUI database.
     *
     * @param array $current
     * @param array $previous
     */
    private function checkAdditions(array $current, array $previous): array
    {
        $this->info(' Checking for additions.');
        $this->info('');

        $additions = [];

        $this->output->progressStart(count($current));

        foreach ($current as $oui) {
            if (!in_array($oui, $previous)) {
                array_push($additions, $oui);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        unset($current);
        unset($previous);

        return $additions;
    }

    /**
     * Check for deletions in the OUI database.
     *
     * @param array $current
     * @param array $previous
     *
     * @return array
     */
    private function checkDeletions(array $current, array $previous): array
    {
        $this->info(' Checking for deletions.');
        $this->info('');

        $deletions = [];

        $this->output->progressStart(count($previous));

        foreach ($previous as $oui) {
            if (!in_array($oui, $current)) {
                array_push($oui, $deletions);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        unset($current);
        unset($previous);

        return $deletions;
    }

    private function checkUpdates(array $current, array $previous)
    {
    }

    /**
     * Query the database for the last file with a different hash than the
     * current one.
     */
    private function selectPreviousFileDetails()
    {
        return DB::table('ieee_oui_files')
            ->where('hash', '<>', $this->file->hash())
            ->where('registry', $this->file->registry())
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
