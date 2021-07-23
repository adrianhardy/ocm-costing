<?php

namespace App\Transformers;

use App\Models\QuoteLine;
use League\Fractal\TransformerAbstract;

class QuoteLineTransformer extends TransformerAbstract
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
    public function transform(QuoteLine $line)
    {
        return $line->only([
            'id', 'packing_instructions', 'barcode',
            'unit_price', 'case_qty', 'case_inner_qty',
            'total_unit_qty',
            'case_volume_cu_m',
            'case_width_m',
            'case_height_m',
            'case_depth_m'
        ]);
    }
}
