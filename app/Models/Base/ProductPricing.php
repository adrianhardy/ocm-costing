<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductPricing
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $product_id
 * @property float $est_landed_cost_per_unit_gbp
 * @property float|null $marketplace_unit_commission_gbp
 * @property float|null $marketplace_unit_fee_gbp
 * @property float|null $unit_shipping_gbp
 * @property float|null $unit_duty_gbp
 * @property float|null $suggested_price_gbp
 * @property float|null $price_gbp
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * 
 * @property User|null $approver
 * @property Product $product
 *
 * @package App\Models\Base
 */
class ProductPricing extends Model
{
	protected $table = 'product_pricing';

	protected $casts = [
		'product_id' => 'int',
		'est_landed_cost_per_unit_gbp' => 'float',
		'marketplace_unit_commission_gbp' => 'float',
		'marketplace_unit_fee_gbp' => 'float',
		'unit_shipping_gbp' => 'float',
		'unit_duty_gbp' => 'float',
		'suggested_price_gbp' => 'float',
		'price_gbp' => 'float',
		'approver_id' => 'int'
	];

	protected $dates = [
		'approved_at'
	];

	public function approver()
	{
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
