<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Acamposm\MacVendorLookup\Exceptions\InvalidMacAddressFormatException;
use Acamposm\MacVendorLookup\Models\OuiAssignment;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FindVendorFromMacAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:vendor {mac : The MAC Address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find the vendor information from a MAC Address';

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
     * @throws Exception
     *
     * @return int
     */
    public function handle()
    {
        $mac_address = $this->argument('mac');

        if (filter_var($mac_address, FILTER_VALIDATE_MAC) === false) {
            throw new InvalidMacAddressFormatException();
        }

        $vendor = self::getVendor(self::getOUI($mac_address));

        $this->info('');

        $this->output->horizontalTable(['OUI', 'MAC Address', 'Vendor', 'Registry'], [
            [
                $vendor[0]['oui'],
                $this->argument('mac'),
                Str::title($vendor[0]['organization']),
                $vendor[0]['registry'],
            ],
        ]);

        return 0;
    }

    /**
     * Returns the OIU for this MAC Address.
     *
     * @param string $mac_address
     *
     * @return string
     */
    private function getOUI(string $mac_address): string
    {
        return substr(str_replace(['.', ':', '-'], '', $mac_address), 0, 6);
    }

    /**
     * Returns the Vendor associated with this OUI.
     *
     * @param string $oui
     *
     * @return array
     */
    private function getVendor(string $oui): array
    {
        return OuiAssignment::select(['oui', 'organization', 'registry'])
            ->where('oui', '=', $oui)
            ->get()
            ->toArray();
    }
}
