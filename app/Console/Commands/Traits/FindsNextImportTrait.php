<?php


namespace App\Console\Commands\Traits;


use App\Models\Import;

trait FindsNextImportTrait
{
    /**
     * @param $type
     * @return Import
     */
    public function getNextImport($type)
    {
        /** @var Import $import */
        if ($this->argument('import')) {
            $import = Import::query()
                ->where('type', $type)
                ->findOrFail($this->argument('import'));
        } else {
            $import = Import::query()
                ->currentStatus(Import::STATUS_PENDING)
                ->where('type', $type)
                ->orderBy('created_at')
                ->first();
        }

        return $import;
    }
}
