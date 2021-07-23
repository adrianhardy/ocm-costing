<?php

namespace App\Transformers;

use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class ImportTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'log'
    ];

    /**
     * @param Import $import
     * @return \League\Fractal\Resource\Primitive
     */
    public function includeLog(Import $import)
    {
        $log = [];
        $file = new \SplFileObject(
            Storage::disk('imports')->path($import->log_path), 'r'
        );
        while (!$file->eof()) {
            // $json can sometimes be null, so we add it conditionally...
            $json = json_decode($file->fgets(), true);
            if ($json) {
                $log[] = $json;
            }
            if (count($log) > 200) {
                break;
            }
        }
        return $this->primitive($log);
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Import $import)
    {
        $json = $import->only([
            'created_at',
            'type',
            'creator',
            'total_rows',
            'current_line'
        ]);
        $json['status'] = $import->latestStatus()->name;
//        $json['rollback_url'] = route('admin.imports.rollback', $import);
//        $json['log_download_url'] = route('admin.imports.show', $import);
        return $json;
    }
}
