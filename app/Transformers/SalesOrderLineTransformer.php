<?php

namespace App\Transformers;

use App\Models\SalesOrderLine;
use League\Fractal\TransformerAbstract;

class SalesOrderLineTransformer extends TransformerAbstract
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
    public function transform(SalesOrderLine $line)
    {
        return $line->only([
            'item_number',
            'article_number',
            'name',
            'packing_instructions',
            'barcode',
            'length_m',
            'width_m',
            'height_m',
            'length_cm',
            'width_cm',
            'height_cm',
            'units_per_case',
            'inner_qty',
            'packing_instructions',
            'product_description',
            'unit_price',
            'case_weight_kg'
        ]);
    }
}
