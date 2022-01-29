<?php

namespace Acamposm\MacVendorLookup\Enums;

class OuiType
{
    public const CID = 'IEEE CID Assignments';
    public const IAB = 'IEEE IAB Assignments';
    public const MAL = 'IEEE MA-L Assignments';
    public const MAM = 'IEEE MA-M Assignments';
    public const MAS = 'IEEE MA-S Assignments';

    public const NAMES = [
        self::CID,
        self::IAB,
        self::MAL,
        self::MAM,
        self::MAS,
    ];
}
