<?php

namespace App\Models;

use App\Models\Base\ProductLandedCost as BaseProductLandedCost;

class ProductLandedCost extends BaseProductLandedCost
{
    use Traits\ProductDimensionMutators;

	protected $fillable = [
		'currency_id',
		'product_id',
        'units_per_case',
		'date',
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
        'height_cm'
    ];

	public function getCaseCubicMAttribute()
    {
        return $this->length_m *
            $this->width_m *
            $this->height_m;
    }

    public function getCaseCubicFtAttribute()
    {
        return round($this->case_cubic_m * setting('cubic_metres_to_feet'), 2);
    }

}
