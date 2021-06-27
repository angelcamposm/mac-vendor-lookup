<?php

namespace Acamposm\MacVendorLookup\Exceptions;

use Exception;

class OuiFileNotFoundException extends Exception
{
    /**
     * OuiFileNotFoundException constructor.
     *
     * @param string $message
     */
    public function __construct($message = 'Oui file not found')
    {
        parent::__construct($message);
    }
}