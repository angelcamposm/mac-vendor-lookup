<?php

namespace Acamposm\MacVendorLookup;

class MacAddress
{
    const DASH = '-';
    const DOT = '.';
    const SEMICOLON = ':';

    /**
     * Array of Common separators on MAC Addresses
     */
    const SEPARATORS = [
        MacAddress::DASH,
        MacAddress::DOT,
        MacAddress::SEMICOLON,
    ];

    protected string $mac;

    /**
     * MacAddress constructor.
     *
     * @param string $mac
     */
    public function __construct(string $mac)
    {
        $this->mac = $mac;
    }

    /**
     * Returns the value of Byte that defines the administration type of the
     * MAC Address
     *
     * @return int
     */
    private function getAdministrationByte(): int
    {
        $bin = self::hex2BinaryRepresentation(
            substr(self::getOui(), 1, 1)
        );

        return substr($bin, 0, 1);
    }

    /**
     * Return the type of administration for this MAC Address
     *
     * @return string
     */
    public function administrationType(): string
    {
        return self::getAdministrationByte() == 0 ? 'UAA' : 'LAA';
    }

    /**
     * Returns the description of the typo of administration of this MAC Address
     *
     * @return string
     */
    public function administrationTypeDescription(): string
    {
        return self::getAdministrationByte() == 0 ?
            'Universally Administered Address' :
            'Locally Administered Address';
    }

    /**
     * Check if the OUI is known as a Virtual Machine Mac Address Ranges from Hypervisors
     *
     * @return bool
     */
    private function checkIsVirtualMachine(): bool
    {
        return in_array(self::getOui(), self::ouiFromHypervisors());
    }

    /**
     * Return the transmission type of the MAC Address
     *
     * @return string
     */
    private function checkTransmissionType(): string
    {
        if (in_array(substr(self::getOui(), 1, 1), ['2', '6', 'A', 'E'])) {
            return 'Unicast';
        }

        if (in_array(substr(self::getOui(), 1, 1), ['3', '7', 'F'])) {
            return 'Multicast';
        }

        return 'Unknown';
    }

    /**
     * Removes from MAC Address the most common separators ('-', '.', ':')
     *
     * @return string
     */
    private function cleanedMacAddress(): string
    {
        return str_replace(self::SEPARATORS, '', $this->mac);
    }

    /**
     * Generates a FAKE MAC Address
     *
     * @param string $format
     * @param int $each
     * @return string
     */
    public static function Fake(string $format = self::SEMICOLON, int $each = 2): string
    {
        $mac = '';

        for($i = 1; $i <= 12; $i++) {
            $mac .= strtoupper(dechex(rand(1, 15)));
        }

        return implode($format, str_split($mac, $each));
    }

    /**
     * Generates $total MAC Address records...
     *
     * @param int $total
     * @param string $format
     * @param int $each
     * @return array
     */
    public static function Generator(int $total, string $format = MacAddress::SEMICOLON, int $each = 2): array
    {
        $macs = [];

        for ($i = 0; $i < $total; $i++) {
            $macs[] = self::Fake($format, $each);
        }

        return filter_var_array($macs, FILTER_VALIDATE_MAC);
    }

    /**
     * Returns the OUI of the MAC Address
     *
     * @return string
     */
    private function getOui(): string
    {
        return substr(self::cleanedMacAddress(), 0, 6);
    }

    /**
     * Returns the value of the M bit
     *
     * @return int
     */
    private function get_m_bit(): int
    {
        $second_bit = substr(self::getOui(), 1, 1);

        return substr(self::hex2BinaryRepresentation($second_bit), 3, 1);
    }

    /**
     * Returns the value of the X bit
     *
     * @return int
     */
    private function get_x_bit(): int
    {
        $second_bit = substr(self::getOui(), 1, 1);

        return substr(self::hex2BinaryRepresentation($second_bit), 2, 1);
    }

    /**
     * Returns the type of Identifier of the OUI
     *
     * @return string
     */
    private function getTypeOfIdentifier(): string
    {
        return self::get_m_bit() == 0 && self::get_x_bit() == 0 ? 'OUI' : 'CID';
    }

    /**
     * Returns a binary representation of a Hexadecimal Bit of a Mac Address
     *
     * @param string $hexBit
     * @return string
     */
    private function hex2BinaryRepresentation(string $hexBit): string
    {
        return decbin(hexdec($hexBit));
    }

    /**
     * Returns the information of the MAC Address as an array
     *
     * @return array
     */
    public function infoToArray(): array
    {
        return self::macAddressInformation();
    }

    /**
     * Returns the information of the MAC Address as a JSON string
     *
     * @return string
     */
    public function infoToJson(): string
    {
        return json_encode(self::macAddressInformation(), JSON_PRETTY_PRINT);
    }

    /**
     * Returns the information of the MAC Address as an Object
     *
     * @return object
     */
    public function infoToObject(): object
    {
        return (object) self::macAddressInformation();
    }

    /**
     * Verifies if the MAC Address is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return filter_var($this->mac, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Return an array with the MAC Address information
     *
     * @return array
     */
    private function macAddressInformation(): array
    {
        return [
            'mac' => [
                'oui' => self::getOui(),
                'address' => self::toFormat(),
                'binary' => self::toBinary(),
            ],
            'is_valid' => self::isValid(),
            'is_virtual_machine' => self::checkIsVirtualMachine(),
            'administration_type' => [
                'value' => self::getAdministrationByte(),
                'type' => self::administrationType(),
                'description' => self::administrationTypeDescription(),
            ],
            'identifier_type' => self::getTypeOfIdentifier(),
            'transmission_type' => self::checkTransmissionType(),
        ];
    }

    /**
     * Return an array with the known OUI from principal Hypervisors in the market
     *
     * @return array
     */
    private function ouiFromHypervisors(): array
    {
        return [
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
    }

    /**
     * Returns a Binary representation of the MAC Address
     *
     * @return string
     */
    public function toBinary(): string
    {
        $bin = '';

        foreach (str_split(self::cleanedMacAddress()) as $bit) {
            $bin .= str_pad(
                self::hex2BinaryRepresentation($bit),
                4,
                '0',
                STR_PAD_LEFT
            );
        }

        return $bin;
    }

    /**
     * Returns a MAC Address formatted as $format input
     *
     * @param string $format
     * @param int $each
     * @return string
     */
    public function toFormat(string $format = self::SEMICOLON, int $each = 2): string
    {
        $mac = '';

        foreach(str_split(self::cleanedMacAddress(), $each) as $octet) {
            if (strlen($mac) === 0) {
                $mac .= $octet;
            } else {
                $mac .= $format.$octet;
            }
        }

        return $mac;
    }

    /**
     * Validates a MAC Address
     *
     * @param string $mac
     * @return bool
     */
    public static function Validate(string $mac): bool
    {
        return filter_var($mac, FILTER_VALIDATE_MAC) != false;
    }
}
