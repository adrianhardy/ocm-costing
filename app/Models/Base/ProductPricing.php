<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductPricing
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $product_id
 * @property float $est_landed_cost_per_unit_gbp
 * @property float|null $marketplace_commission_pc
 * @property float|null marketplace_fee
 * @property float|null $profit
 * @property float|null $suggested_unit_price_gbp
 * @property float|null $unit_shipping_gbp
 * @property float|null $unit_price_gbp
 * @property float|null $gross_unit_cost_gbp
 * @property float|null $margin_pc
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * @property Carbon|null $superseded_at
 * 
 * @property User|null $approver
 * @property Product $product
 *
 * @package App\Models\Base
 */
class ProductPricing extends Model
{
	use SoftDeletes;
	protected $table = 'product_pricing';

	protected $casts = [
		'product_id' => 'int',
		'est_landed_cost_per_unit_gbp' => 'float',
		'marketplace_commission_pc' => 'float',
		'suggested_unit_price_gbp' => 'float',
		'unit_shipping_gbp' => 'float',
		'unit_price_gbp' => 'float',
		'gross_unit_cost_gbp' => 'float',
		'margin_pc' => 'float',
		'approver_id' => 'int'
	];

	protected $dates = [
		'approved_at',
		'superseded_at'
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
