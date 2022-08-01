<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use App\Models\ProductPricingCountry;
use App\Models\SalesOrderLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
 * @property float|null $gross_unit_cost_gbp
 * @property Carbon|null $submitted_at
 * @property int|null $submitter_id
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * @property Carbon|null $superseded_at
 * @property int|null $sales_order_line_id
 * @property float|null $eur_fx_rate
 * 
 * @property User|null $approver
 * @property Product $product
 * @property SalesOrderLine|null $sales_order_line
 * @property User|null $submitter
 * @property Collection|ProductPricingCountry[] $product_pricing_countries
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
		'gross_unit_cost_gbp' => 'float',
		'submitter_id' => 'int',
		'approver_id' => 'int',
		'sales_order_line_id' => 'int',
		'eur_fx_rate' => 'float'
	];

	protected $dates = [
		'submitted_at',
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

	public function sales_order_line()
	{
		return $this->belongsTo(SalesOrderLine::class);
	}

	public function submitter()
	{
		return $this->belongsTo(User::class, 'submitter_id');
	}

	public function product_pricing_countries()
	{
		return $this->hasMany(ProductPricingCountry::class);
	}
}
