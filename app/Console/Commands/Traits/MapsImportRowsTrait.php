<?php


namespace App\Console\Commands\Traits;


use App\Models\Import;

trait MapsImportRowsTrait
{

    /**
     * @param Import $import
     * @param array $row
     * @return array
     */
    protected function mapRow(Import $import, array $row): array
    {
        $mapped = [];
        foreach ($import->mapping as $column => $name) {
            if (isset($row[$column])) {
                $mapped[$name] = $row[$column];
            }
        }
        return $mapped;
    }
}
