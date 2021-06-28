<?php

namespace Acamposm\MacVendorLookup\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OuiAssignment.
 *
 *
 * @property string $oui
 * @property string $organization
 * @property string $address
 * @property string $registry
 */
class OuiAssignment extends Model
{
    protected $table = 'ieee_oui_assignments';

    /**
     * Return the block size of the registry.
     *
     * @return array
     */
    public function blockSize(): array
    {
        return match ($this->registry) {
            'MA-L' => [
                'assignment' => '2^24',
                'total'      => number_format(16777216, 0),
            ],
            'MA-M' => [
                'assignment' => '2^20',
                'total'      => number_format(1048576, 0),
            ],
            'MA-S' => [
                'assignment' => '2^12',
                'total'      => number_format(4096, 0),
            ],
            default => [],
        };
    }

    /**
     * Return the ranges.
     *
     * @return object
     */
    public function ranges()
    {
        return match ($this->registry) {
            'MA-L' => (object) [
                'lower' => self::formatMacAddress($this->oui.'000000'),
                'upper' => self::formatMacAddress($this->oui.'FFFFFF'),
            ],
            'MA-M' => (object) [
                'lower' => self::formatMacAddress($this->oui.'000000'),
                'upper' => self::formatMacAddress($this->oui.'FFFFFF'),
            ],
            'MA-S' => (object) [
                'lower' => self::formatMacAddress($this->oui.'000000'),
                'upper' => self::formatMacAddress($this->oui.'FFFFFF'),
            ],
        };
    }

    /**
     * Returns a formated MAC Address.
     *
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    private function formatMacAddress(string $input, string $separator = ':'): string
    {
        return implode($separator, str_split($input, 2));
    }

    /**
     * Returns if the OUI Assigment is a private assigment.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->organization === 'Private';
    }

    /**
     * Verifies if is a Virtual Machine based on Vendor OUI.
     *
     * @return bool
     */
    public function isVirtualMachine(): bool
    {
        $oui_from_vendors = [
            // Microsoft Corporation
            '00155D', '0003FF',
            // Nutanix
            '506B8D', 'B47947', 'E01995',
            // Parallels, Inc.
            '001C42',
            // Sun Virtual Box
            '080027', '0A0027',
            // VMware, Inc.
            '001C14', '000C29', '005056', '000569',
            // Xensource, Inc.
            '00163E',
            // QEMU (Unknown vendor)
            '525400',
        ];

        return in_array($this->oui, $oui_from_vendors);
    }

    /**
     * Return the administration type of the MAC Address.
     *
     * @return string[]
     */
    public function administrationByte(): array
    {
        $first_octet = substr($this->oui, 1, 1);

        $bin = sprintf('%04d', decbin(hexdec($first_octet)));

        return substr($bin, 0, 1) == 0 ? [
            'type'        => 'UAA',
            'description' => 'Universally Administered Address',
        ] : [
            'type'        => 'LAA',
            'description' => 'Locally Administered Address',
        ];
    }

    public function typeOfIdentifier()
    {
        $second_bit = substr($this->oui, 1, 1);

        $mbit = substr(sprintf('%04d', decbin(hexdec($second_bit))), 3, 1);
        $xbit = substr(sprintf('%04d', decbin(hexdec($second_bit))), 2, 1);

        return $mbit == 0 && $xbit == 0 ? 'OUI' : 'CID';
    }

    /**
     * Return if the byte is set to Individual or Group.
     *
     * @return string
     */
    public function groupByte(): string
    {
        $first_octet = substr($this->oui, 0, 1);

        return substr($first_octet, 0, 1) == 0 ? 'Individual address' : 'Group address';
    }

    /**
     * Verifies if the MAC Address is a Multicast MAC Address.
     *
     * @return bool
     */
    public function isMulticast(): bool
    {
        return in_array(substr($this->oui, 1, 1), ['3', '7', 'F']);
    }

    /**
     * Verifies if the MAC Address is a Unicast MAC Address.
     *
     * @return bool
     */
    public function isUnicast(): bool
    {
        return in_array(substr($this->oui, 1, 1), ['2', '6', 'A', 'E']);
    }
}
