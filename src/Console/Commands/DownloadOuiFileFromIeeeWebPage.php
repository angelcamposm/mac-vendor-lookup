<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Acamposm\MacVendorLookup\Exceptions\OuiFileNotFoundException;
use Acamposm\MacVendorLookup\OuiFile;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DownloadOuiFileFromIeeeWebPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:download {--file= : The file to download} {--manual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads the OUI csv file from the IEEE web page.';

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
     * @throws OuiFileNotFoundException
     * @throws Exception
     *
     * @return int
     */
    public function handle()
    {
        $choice = $this->option('file') ?? self::drawMenu();

        self::createDirectory();

        if ($choice === 'All') {
            foreach (OuiFile::NAMES as $file) {
                self::downloadRecipe($file, $this->option('manual'));
            }
        } else {
            self::downloadRecipe($choice, $this->option('manual'));
        }

        return 0;
    }

    /**
     * Create initial directory if needed.
     */
    private function createDirectory(): void
    {
        if (!Storage::exists('ieee')) {
            $this->info(' Creating initial directory.');

            Storage::makeDirectory('ieee');
        }
    }

    /**
     * Displays a menu to select the file to download.
     *
     * @return string
     */
    private function drawChoices(): string
    {
        return $this->choice('Select a file to download', [...OuiFile::NAMES, 'All']);
    }

    /**
     * Displays the menu to select a file to download.
     *
     * @return string
     */
    private function drawMenu(): string
    {
        $this->info('');

        self::drawTitle();

        return self::drawChoices();
    }

    /**
     * Draws the title of the command.
     */
    private function drawTitle()
    {
        $this->comment('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->comment('│                    IEEE MAC Address Assignments Download                    │');
        $this->comment('└─────────────────────────────────────────────────────────────────────────────┘');
    }

    /**
     * Recipe to download a file.
     *
     * @param string $file
     * @param bool   $isManualAssigment
     *
     * @throws OuiFileNotFoundException
     */
    private function downloadRecipe(string $file, bool $isManualAssigment)
    {
        $this->file = new OuiFile($file, $isManualAssigment);

        self::fileDownload();

        self::storeFileDetails();

//        if ($this->confirm('Do you wish to insert records in the database')) {
//            $this->call('mac:insert', ['file' => $this->file->name()]);
//        }
    }

    /**
     * Downloads the file and stores in storage.
     */
    private function fileDownload(): void
    {
        $this->info(' Downloading OUI file from IEEE web page');

        Storage::put($this->file->path(), fopen($this->file->url(), 'r'));
    }

    /**
     * Return an array with the details of the file downloaded.
     *
     * @return array
     */
    private function newDownloadRecord(): array
    {
        return [
            'name'       => $this->file->name(),
            'size'       => $this->file->size(),
            'hash'       => $this->file->hash(),
            'registry'   => $this->file->registry(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Store file details in the database.
     *
     * @throws OuiFileNotFoundException
     */
    private function storeFileDetails(): void
    {
        if (!$this->file->exists()) {
            throw new OuiFileNotFoundException('File '.$this->file->name().' not found.');
        }

        $record = DB::table('ieee_oui_files')
            ->where('name', $this->file->name())
            ->get();

        if ($record->count() == 0) {
            $this->info(' File downloaded and stored as '.$this->file->name());

            DB::table('ieee_oui_files')->insert(self::newDownloadRecord());
        }
    }
}
