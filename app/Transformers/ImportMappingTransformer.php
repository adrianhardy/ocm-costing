<?php

namespace App\Transformers;

use App\Models\ImportMapping;
use League\Fractal\TransformerAbstract;

class ImportMappingTransformer extends TransformerAbstract
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
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ImportMapping $importMapping)
    {
        return $importMapping->only([
            'id', 'name', 'mapping'
        ]);
    }
}
