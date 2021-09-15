<?php

namespace App\Models;

use App\Models\Base\SalesOrderLine as BaseSalesOrderLine;

/**
 * Class SalesOrderLine
 * @package App\Models
 * @property-read $estimated_shipping_cost_gbp
 * @property-read $worst_estimated_landed_cost_data
 * @property $total_cubic_ft
 */
class SalesOrderLine extends BaseSalesOrderLine
{
	protected $fillable = [
		'sales_order_id',
		'item_number',
		'article_number',
		'product_id',
		'units_requested',
        'packing_instructions',
        'product_description',
		'unit_price',
		'unit_price_gbp',
		'total',
		'total_gbp',
        'total_cubic_ft',
        'total_cubic_m'
	];

	public function getEstimatedShippingCostGbpAttribute()
    {
	    return $this->total_cubic_ft
            * setting('shipping_cost_per_cubic_foot_usd')
            * setting('exchange_rate_usd');
    }


    public function getTotalCubicFtAttribute()
    {
        return round($this->total_cubic_m * setting('cubic_metres_to_feet'), 2);
    }

    public function setTotalCubicFtAttribute($ft)
    {
        return $this->total_cubic_m = $ft / setting('cubic_metres_to_feet');
    }

    /**
     * @return array|null
     */
    public function getWorstEstimatedLandedCostDataAttribute()
    {
        $consignment = $this->consignments()
            ->orderBy('est_landed_cost_per_unit_gbp', 'DESC')
            ->first();
        return optional($consignment)->pivot;
    }

}
