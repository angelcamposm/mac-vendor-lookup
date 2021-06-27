<?php

namespace Acamposm\MacVendorLookup;

use Acamposm\MacVendorLookup\Exceptions\OuiFileNotFoundException;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class OuiFile
{
    const FOLDER = 'ieee';

    const CID = 'IEEE CID Assignments';
    const IAB = 'IEEE IAB Assignments';
    const MAL = 'IEEE MA-L Assignments';
    const MAM = 'IEEE MA-M Assignments';
    const MAS = 'IEEE MA-S Assignments';

    const NAMES = [
        OuiFile::CID,
        OuiFile::IAB,
        OuiFile::MAL,
        OuiFile::MAM,
        OuiFile::MAS,
    ];

    protected bool $manualAssignment = false;
    protected string $file;
    protected string $name;

    /**
     * OuiFile constructor.
     *
     * @param ?string $name
     * @param bool $isManualAssignment
     *
     * @throws Exception
     */
    public function __construct(?string $name = null, bool $isManualAssignment = false)
    {
        if (is_null($name)) {
            throw new Exception('A file type or name is required');
        }

        if (!in_array($name, OuiFile::NAMES) && $isManualAssignment == false) {
            throw new Exception('For custom name, $isManualAssigment must be true.');
        }

        if ($isManualAssignment) {
            $this->file = $name;
            $this->manualAssignment = true;
        } else {
            $this->file = self::getFileName($name);
            $this->name = $name;
        }
    }

    /**
     * Returns a string with date stamp.
     *
     * @return string
     */
    public function date(): string
    {
        if ($this->manualAssignment) {
            return Carbon::createFromFormat('Ymd', substr($this->file, 8, 8))->toDateString();
        } else {
            return Carbon::now()->format('Ymd');
        }
    }

    /**
     * Check if file exists.
     *
     * @return bool
     * @throws OuiFileNotFoundException
     */
    public function exists(): bool
    {
        if (!Storage::exists(self::path())) {
            throw new OuiFileNotFoundException('File '.self::name().' not found.');
        }

        return Storage::exists(self::path());
    }

    /**
     * Return the hash of the file
     *
     * @return string
     */
    public function hash(): string
    {
        return (new Filesystem)->hash(storage_path(self::fullPath()));
    }

    /**
     * Creates the final file name
     *
     * @param string $name
     * @return string
     */
    public function getFileName(string $name): string
    {
        switch ($name) {
            case OuiFile::MAL:
                return 'oui_mal_'.self::date().'.csv';
            case OuiFile::MAM:
                return 'oui_mam_'.self::date().'.csv';
            case OuiFile::MAS:
                return 'oui_mas_'.self::date().'.csv';
            case OuiFile::IAB:
                return 'oui_iab_'.self::date().'.csv';
            case OuiFile::CID:
                return 'oui_cid_'.self::date().'.csv';
            default:
                return '';
        }
    }

    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->file;
    }

    /**
     * Return the file path.
     *
     * @return string
     */
    public function path(): string
    {
        return self::FOLDER.'/'.self::name();
    }

    /**
     * Return the type of registry for this file.
     *
     * @return string
     */
    public function registry(): string
    {
        if (!isset($this->name)) {
            return self::getRegistryFromFileName($this->file);
        }

        switch ($this->name) {
            case OuiFile::MAL:
                return 'MA-L';
            case OuiFile::MAM:
                return 'MA-M';
            case OuiFile::MAS:
                return 'MA-S';
            case OuiFile::IAB:
                return 'IAB';
            case OuiFile::CID:
                return 'CID';
            default:
                return '';
        }
    }

    /**
     * Returns the Registry Type from the given file name
     *
     * @param string $name
     * @return string
     */
    private function getRegistryFromFileName(string $name): string
    {
        switch (substr($name, 4, 3)) {
            case 'cid':
                return 'CID';
            case 'iab':
                return 'IAB';
            case 'mal':
                return 'MA-L';
            case 'mam':
                return 'MA-M';
            case 'mas':
                return 'MA-S';
        }
    }

    /**
     * Return the full file path.
     *
     * @return string
     */
    public function fullPath(): string
    {
        return 'app/'.self::path();
    }

    /**
     * Get the size of the file.
     *
     * @return int
     */
    public function size(): int
    {
        return Storage::size(self::path());
    }

    /**
     * Returns the url for download the file oui.csv.
     *
     * @return string
     */
    public function url(): string
    {
        switch ($this->name) {
            case OuiFile::MAL:
                return config('ieee.oui.url.mal');
            case OuiFile::MAM:
                return config('ieee.oui.url.mam');
            case OuiFile::MAS:
                return config('ieee.oui.url.mas');
            case OuiFile::IAB:
                return config('ieee.oui.url.iab');
            case OuiFile::CID:
                return config('ieee.oui.url.cid');
            default:
                return '';
        }
    }
}
