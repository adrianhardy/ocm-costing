<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

/**
 * Class ProductPricing
 * @package App\Models
 * @property-read $calculated_gross_profit_gbp
 */
class ProductPricing extends BaseProductPricing
{
	protected $fillable = [
		'product_id',
		'est_landed_cost_per_unit_gbp',
		'marketplace_commission_pc',
		'suggested_unit_price_gbp',
		'unit_shipping_gbp',
		'unit_price_gbp',
		'gross_unit_cost_gbp',
		'margin_pc',
		'approved_at',
		'approver_id',
		'superseded_at',
		'marketplace_fee',
		'profit'
	];

	public function getCalculatedGrossProfitGbpAttribute()
    {
        $profit = $this->unit_price_gbp;
        $profit -= ($this->unit_price_gbp * ($this->marketplace_commission_pc / 100));
        $profit -= $this->marketplace_fee;
        $profit -= $this->est_landed_cost_per_unit_gbp;
        $profit -= $this->unit_shipping_gbp;
        return $profit;
    }
}
