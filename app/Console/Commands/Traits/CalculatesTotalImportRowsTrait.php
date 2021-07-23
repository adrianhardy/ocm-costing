<?php


namespace App\Console\Commands\Traits;


use App\Models\Import;

trait CalculatesTotalImportRowsTrait
{

    /**
     * @param Import $import
     */
    protected function calculateTotalRows(Import $import)
    {
        $csv = $import->getSplFileObject();
        $this->skipHeaderRows($import, $csv);
        $import->total_rows = 0;
        while (!$csv->eof()) {
            $row = $csv->fgetcsv();
            if (!$row || trim(implode('', $row)) == '') {
                continue;
            }
            $import->total_rows++;
        }
        $import->save();
    }

    /**
     * @param Import $import
     * @param \SplFileObject $csv
     */
    protected function skipHeaderRows(Import $import, \SplFileObject $csv)
    {
        for ($i = 0; $i < $import->skip_header_rows; $i++) {
            $csv->fgetcsv();
        }
    }
}
