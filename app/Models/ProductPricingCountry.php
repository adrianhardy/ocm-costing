<?php

namespace App\Models;

use App\Models\Base\ProductPricingCountry as BaseProductPricingCountry;

/**
 * @property float $current_gbp_fx_rate
 */
class ProductPricingCountry extends BaseProductPricingCountry
{
	protected $fillable = [
		'country_id',
		'product_pricing_id',
		'gbp_fx_rate',
		'vat_rate',
		'unit_vat',
		'unit_fba_price',
		'unit_fba_fee',
		'unit_sf_price',
		'unit_sf_shipping',
		'unit_fba_shipping'
	];

    public function setCurrentGbpFxRateAttribute($value)
    {
        $this->attributes['gbp_fx_rate'] = round(1 / $value, 4);
    }

    public function getCurrentGbpFxRateAttribute()
    {
        if ($this->gbp_fx_rate == 0) {
            return round(0, 2);
        }
        return round(1 / $this->gbp_fx_rate, 2);
    }

}
