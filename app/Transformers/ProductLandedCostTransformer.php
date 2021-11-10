<?php

namespace App\Transformers;

use App\Models\Base\ProductLandedCost;
use League\Fractal\TransformerAbstract;

class ProductLandedCostTransformer extends TransformerAbstract
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
        'currency'
    ];

    public function includeCurrency(ProductLandedCost $cost)
    {
        return $this->item($cost->currency, new CurrencyTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ProductLandedCost $cost)
    {
        return $cost->only([
            'date',
            'units_per_case',
            'length_m',
            'height_m',
            'width_m',
            'fx_rate',
            'shipping_cost_per_cuft_usd',
            'import_duty_pc',
            'commission_rate_pc',
            'landed_cost_per_unit',
            'landed_cost_per_unit_gbp',
            'unit_price',
            'length_cm',
            'width_cm',
            'height_cm',
            'case_cubic_ft',
            'case_cubic_m'
        ]);
    }
}
