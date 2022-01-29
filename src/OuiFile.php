<?php

namespace Acamposm\MacVendorLookup;

use Acamposm\MacVendorLookup\Enums\OuiType;
use Acamposm\MacVendorLookup\Exceptions\OuiFileNotFoundException;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class OuiFile
{
    public const FOLDER = 'ieee';
    protected bool $manualAssignment = false;
    protected string $file;
    protected string $name;

    /**
     * OuiFile constructor.
     *
     * @param ?string $name
     * @param bool    $isManualAssignment
     *
     * @throws Exception
     */
    public function __construct(?string $name = null, bool $isManualAssignment = false)
    {
        if (is_null($name)) {
            throw new \RuntimeException('A file type or name is required');
        }

        if ((false === $isManualAssignment) && !in_array($name, OuiType::NAMES)) {
            throw new \RuntimeException('For custom name, $isManualAssigment must be true.');
        }

        if ($isManualAssignment) {
            $this->file = $name;
            $this->manualAssignment = true;
        } else {
            $this->file = $this->getFileName($name);
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
        }

        return Carbon::now()->format('Ymd');
    }

    /**
     * Check if file exists.
     *
     * @throws OuiFileNotFoundException
     *
     * @return bool
     */
    public function exists(): bool
    {
        if (!Storage::exists($this->path())) {
            throw new OuiFileNotFoundException('File '.$this->name().' not found.');
        }

        return Storage::exists($this->path());
    }

    /**
     * Return the hash of the file.
     *
     * @return string
     */
    public function hash(): string
    {
        return (new Filesystem())->hash(storage_path($this->fullPath()));
    }

    /**
     * Creates the final file name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getFileName(string $name): string
    {
        return match ($name) {
            OuiType::CID => 'oui_cid_'.$this->date().'.csv',
            OuiType::IAB => 'oui_iab_'.$this->date().'.csv',
            OuiType::MAL => 'oui_mal_'.$this->date().'.csv',
            OuiType::MAM => 'oui_mam_'.$this->date().'.csv',
            OuiType::MAS => 'oui_mas_'.$this->date().'.csv',
        };
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
        return self::FOLDER.'/'.$this->name();
    }

    /**
     * Return the type of registry for this file.
     *
     * @return string
     */
    public function registry(): string
    {
        if (!isset($this->name)) {
            return $this->getRegistryFromFileName($this->file);
        }

        return match ($this->name) {
            OuiType::CID => 'CID',
            OuiType::IAB => 'IAB',
            OuiType::MAL => 'MA-L',
            OuiType::MAM => 'MA-M',
            OuiType::MAS => 'MA-S',
        };
    }

    /**
     * Returns the Registry Type from the given file name.
     *
     * @param string $name
     *
     * @return string
     */
    private function getRegistryFromFileName(string $name): string
    {
        return match (substr($name, 4, 3)) {
            'cid' => 'CID',
            'iab' => 'IAB',
            'mal' => 'MA-L',
            'mam' => 'MA-M',
            'mas' => 'MA-S',
        };
    }

    /**
     * Return the full file path.
     *
     * @return string
     */
    public function fullPath(): string
    {
        return 'app/'.$this->path();
    }

    /**
     * Get the size of the file.
     *
     * @return int
     */
    public function size(): int
    {
        return Storage::size($this->path());
    }

    /**
     * Returns the url for download the file oui.csv.
     *
     * @return string
     */
    public function url(): string
    {
        return match ($this->name) {
            OuiType::CID => config('ieee.oui.url.cid'),
            OuiType::IAB => config('ieee.oui.url.iab'),
            OuiType::MAL => config('ieee.oui.url.mal'),
            OuiType::MAM => config('ieee.oui.url.mam'),
            OuiType::MAS => config('ieee.oui.url.mas'),
        };
    }
}
