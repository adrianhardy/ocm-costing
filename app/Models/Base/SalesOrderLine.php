<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Consignment;
use App\Models\Product;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Versionable\VersionableTrait;

/**
 * Class SalesOrderLine
 * 
 * @property int $id
 * @property int|null $sales_order_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $item_number
 * @property string|null $article_number
 * @property string|null $packing_instructions
 * @property string|null $product_description
 * @property string|null $name
 * @property int|null $product_id
 * @property int $units_requested
 * @property float|null $unit_price
 * @property float|null $unit_price_gbp
 * @property float|null $total
 * @property float|null $total_gbp
 * @property float|null $total_cubic_m
 * 
 * @property Product|null $product
 * @property SalesOrder|null $sales_order
 * @property Collection|Consignment[] $consignments
 *
 * @package App\Models\Base
 */
class SalesOrderLine extends Model
{
	use SoftDeletes;
	protected $table = 'sales_order_lines';

	protected $casts = [
		'sales_order_id' => 'int',
		'product_id' => 'int',
		'units_requested' => 'int',
		'unit_price' => 'float',
		'unit_price_gbp' => 'float',
		'total' => 'float',
		'total_gbp' => 'float',
		'total_cubic_m' => 'float'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function sales_order()
	{
		return $this->belongsTo(SalesOrder::class);
	}

	public function consignments()
	{
		return $this->belongsToMany(Consignment::class, 'sales_order_lines_consignments')
					->withPivot('qty_shipped', 'goods_cost', 'goods_cost_gbp', 'shipping_cost_gbp', 'shipping_cost_gbp_per_unit', 'invoice_amt', 'est_landed_cost_per_unit', 'est_landed_cost_per_unit_gbp');
	}
}
