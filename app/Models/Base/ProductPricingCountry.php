<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Country;
use App\Models\ProductPricing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductPricingCountry
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $country_id
 * @property int $product_pricing_id
 * @property float|null $gbp_fx_rate
 * @property float|null $vat_rate
 * @property float|null $unit_fba_price
 * @property float|null $unit_fba_fee
 * @property float|null $unit_sf_price
 * @property float|null $unit_sf_shipping
 * @property float|null $unit_fba_shipping
 * 
 * @property Country $country
 * @property ProductPricing $product_pricing
 *
 * @package App\Models\Base
 */
class ProductPricingCountry extends Model
{
	protected $table = 'product_pricing_countries';

	protected $casts = [
		'country_id' => 'int',
		'product_pricing_id' => 'int',
		'gbp_fx_rate' => 'float',
		'vat_rate' => 'float',
		'unit_fba_price' => 'float',
		'unit_fba_fee' => 'float',
		'unit_sf_price' => 'float',
		'unit_sf_shipping' => 'float',
		'unit_fba_shipping' => 'float'
	];

	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	public function product_pricing()
	{
		return $this->belongsTo(ProductPricing::class);
	}
}
