<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Currency;
use App\Models\ProductPricingCountry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $name
 * @property string $iso_2_code
 * @property float $import_duty
 * @property float $export_duty
 * @property float|null $vat_rate
 * @property int|null $currency_id
 * @property bool|null $has_pricing
 * 
 * @property Currency|null $currency
 * @property Collection|ProductPricingCountry[] $product_pricing_countries_country
 *
 * @package App\Models\Base
 */
class Country extends Model
{
	protected $table = 'countries';
	public $timestamps = false;

	protected $casts = [
		'import_duty' => 'float',
		'export_duty' => 'float',
		'vat_rate' => 'float',
		'currency_id' => 'int',
		'has_pricing' => 'bool'
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function product_pricing_countries_country()
	{
		return $this->hasMany(ProductPricingCountry::class);
	}
}
