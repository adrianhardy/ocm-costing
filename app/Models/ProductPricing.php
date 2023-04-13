<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

/**
 * Class ProductPricing
 * @package App\Models
 * @property float $marketplace_fee
 * @property float $current_eur_fx_rate
 */

class ProductPricing extends BaseProductPricing
{
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

	protected $fillable = [
		'product_id',
		'est_landed_cost_per_unit_gbp',
		'marketplace_commission_pc',
		'unit_shipping_gbp',
		'unit_price_gbp',
		'gross_unit_cost_gbp',
		'approved_at',
		'approver_id',
		'superseded_at',
		'marketplace_fee',
		'sales_order_line_id',
        'eur_fx_rate',
        'current_eur_fx_rate',
        'priority'
	];

    /**
     * Get the inverse of the GBP:EUR
     *
     * @return float
     */
    public function getCurrentEurFxRateAttribute()
    {
        if ($this->eur_fx_rate == 0) {
            return round(0, 2);
        }
        return round(1 / $this->eur_fx_rate, 2);
    }

    public function setCurrentEurFxRateAttribute($value)
    {
        $this->attributes['eur_fx_rate'] = round(1 / $value, 4);
    }

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
