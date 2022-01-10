<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Currency;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductLandedCost
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $currency_id
 * @property int $product_id
 * @property Carbon $date
 * @property int $units_per_case
 * @property float $unit_price
 * @property float|null $length_m
 * @property float|null $height_m
 * @property float|null $width_m
 * @property float $fx_rate
 * @property float $shipping_cost_per_cuft
 * @property float $import_duty_pc
 * @property float $commission_rate_pc
 * @property float $landed_cost_per_unit
 * @property float $landed_cost_per_unit_gbp
 * 
 * @property Currency|null $currency
 * @property Product $product
 *
 * @package App\Models\Base
 */
class ProductLandedCost extends Model
{
	protected $table = 'product_landed_costs';

	protected $casts = [
		'currency_id' => 'int',
		'product_id' => 'int',
		'units_per_case' => 'int',
		'unit_price' => 'float',
		'length_m' => 'float',
		'height_m' => 'float',
		'width_m' => 'float',
		'fx_rate' => 'float',
		'shipping_cost_per_cuft' => 'float',
		'import_duty_pc' => 'float',
		'commission_rate_pc' => 'float',
		'landed_cost_per_unit' => 'float',
		'landed_cost_per_unit_gbp' => 'float'
	];

	protected $dates = [
		'date'
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
