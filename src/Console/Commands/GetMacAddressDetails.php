<?php

namespace Acamposm\MacVendorLookup\Console\Commands;

use Acamposm\MacVendorLookup\Exceptions\InvalidMacAddressFormatException;
use Acamposm\MacVendorLookup\Models\OuiAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GetMacAddressDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:details {mac}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get MAC Address details of a given MAC Address';

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
        $mac_address = $this->argument('mac');

        if (filter_var($mac_address, FILTER_VALIDATE_MAC) === false) {
            throw new InvalidMacAddressFormatException();
        }

        $vendor = self::getMacAddressDetails(self::getOUI($mac_address));

        $this->info('');

        self::drawVendorTable($vendor);

        self::drawBlockTable($vendor);

        self::drawMacTable($vendor);
//        $this->output->horizontalTable([
//            'OUI',
//            'MAC Address',
//            'Vendor',
//            'Address',
//            'Registry',
//            'Assignment bits',
//            'Block Size',
//            'Private',
//            'Virtual Machine',
//            'Is Multicast',
//            'Is Unicast',
//            'Last Update',
//        ], [
//            [
//                $vendor->oui,
//                $this->argument('mac'),
//                Str::title($vendor->organization),
//                $vendor->address,
//                $vendor->registry,
//                ($vendor->blockSize())['assignment'],
//                ($vendor->blockSize())['total'],
//                $vendor->isPrivate() ? 'true' : 'false',
//                $vendor->isVirtualMachine() ? 'true' : 'false',
//                $vendor->isMulticast() ? 'true' : 'false',
//                $vendor->isUnicast() ? 'true' : 'false',
//                $vendor->updated_at,
//            ]
//        ]);
        return 0;
    }

    /**
     * Draws the Vendor Details Table
     *
     * @param OuiAssignment $assignment
     */
    private function drawVendorTable(OuiAssignment $assignment): void
    {
        $this->info(' Vendor details');

        $this->output->horizontalTable([
            'OUI',
            'MAC Address',
            'Vendor',
            'Address',
            'Is Private',
        ], [
            [
                $assignment->oui,
                $this->argument('mac'),
                Str::title($assignment->organization),
                $assignment->address,
                $assignment->isPrivate() ? 'true' : 'false',
            ]
        ]);
    }

    /**
     * Draws the Assigment Block Details Table
     *
     * @param OuiAssignment $assignment
     */
    private function drawBlockTable(OuiAssignment $assignment): void
    {
        $this->info(' Block details');

        $this->output->horizontalTable([
            'Registry',
            'Assignment bits',
            'Block Size',
            'Lower MAC Address',
            'Upper MAC Address',
            'Last Update',
        ], [
            [
                $assignment->registry,
                ($assignment->blockSize())['assignment'],
                ($assignment->blockSize())['total'],
                ($assignment->ranges())->lower,
                ($assignment->ranges())->upper,
                $assignment->updated_at ?? 'Unknown',
            ]
        ]);
    }

    /**
     * Draws the Mac Address Details Table
     *
     * @param OuiAssignment $assignment
     */
    private function drawMacTable(OuiAssignment $assignment): void
    {
        $this->info(' MAC Address details');

        $this->output->horizontalTable([
            'MAC Address',
            'Administration byte',
            'Group byte',
            'Virtual Machine',
            'Is Multicast',
            'Is Unicast',
            'Is Valid',
        ], [
            [
                $this->argument('mac'),
                ($assignment->administrationBype())['type'].' ('.($assignment->administrationBype())['description'].')',
                $assignment->groupByte(),
                $assignment->isVirtualMachine() ? 'true' : 'false',
                $assignment->isMulticast() ? 'true' : 'false',
                $assignment->isUnicast() ? 'true' : 'false',
                filter_var($this->argument('mac'), FILTER_VALIDATE_MAC) != false ? 'true' : 'false',
            ]
        ]);
    }

    /**
     * Returns the OIU for this MAC Address
     *
     * @param string $mac_address
     * @return string
     */
    private function getOUI(string $mac_address): string
    {
        return substr(str_replace(['.',':','-'], '', $mac_address), 0, 6);
    }

    /**
     * Returns the Vendor associated with this OUI
     *
     * @param string $oui
     * @return array
     */
    private function getMacAddressDetails(string $oui)
    {
        return OuiAssignment::where('oui', '=', $oui)->first();
    }
}
