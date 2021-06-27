<?php

namespace Acamposm\MacVendorLookup;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OuiFileProcessor
{
    protected string $file;
    protected array $csv;

    /**
     * Load the specified file into an array
     *
     * @param string $path
     * @return $this
     */
    public function load(string $path): OuiFileProcessor
    {
        $this->csv = self::process(array_map('str_getcsv', file(storage_path($path))));
        $this->file = $path;

        return $this;
    }

    public function toArray(): array
    {
        return $this->csv;
    }

    public function toCollection(): Collection
    {
        return Collection::make($this->csv);
    }

    /**
     * Process the array, removes headers and add custom ones
     *
     * @param array $csv
     * @return array
     */
    private function process(array $csv): array
    {
        array_shift($csv);

        $column_names = ['registry', 'oui', 'organization', 'address', 'created_at'];

        array_walk($csv, function (&$value) use ($column_names) {
            $value = array_combine($column_names, [...$value, Carbon::now()->toDateTimeString()]);
        });

        return $csv;
    }
}
