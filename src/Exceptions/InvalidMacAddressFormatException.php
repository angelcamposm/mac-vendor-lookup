<?php

namespace Acamposm\MacVendorLookup\Exceptions;

use Exception;

class InvalidMacAddressFormatException extends Exception
{
    /**
     * InvalidMacAddressFormatException constructor.
     *
     * @param string $message
     */
    public function __construct($message = 'Invalid MAC Address format')
    {
        parent::__construct($message);
    }
}
