<?php

namespace App\Transformers;

use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\DB;
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
        'product', 'landed_cost'
    ];

    public function includeLandedCost(SalesOrderLine $line)
    {
        $consignment = $line->consignments()
            ->orderBy('est_landed_cost_per_unit_gbp', 'DESC')
            ->first();

        if (!$consignment) {
            return $this->null();
        }

        return $this->primitive($consignment->pivot);
    }

    public function includeProduct(SalesOrderLine $line)
    {
        if (!$line->product) {
            return $this->null();
        }
        return $this->item($line->product, new ProductTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(SalesOrderLine $line)
    {
        return $line->only([
            'id',
            'item_number',
            'article_number',
            'name',
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
            'case_weight_kg',
            'units_requested',
            'total',
            'total_gbp',
            'total_cubic_m',
            'total_cubic_ft',
            'estimated_shipping_cost_gbp'
        ]);
    }
}
