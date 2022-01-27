<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Consignment;
use App\Models\InspectionReport;
use App\Models\Medium;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @property int|null $units_requested
 * @property string|null $barcode
 * @property int|null $units_per_case
 * @property int|null $inner_qty
 * @property float|null $case_weight_kg
 * @property float|null $height_m
 * @property float|null $length_m
 * @property float|null $width_m
 * @property float|null $unit_price
 * @property float|null $unit_price_gbp
 * @property float|null $total
 * @property float|null $total_gbp
 * @property float|null $total_cubic_m
 * @property float|null $unit_width_m
 * @property float|null $unit_length_m
 * @property float|null $unit_height_m
 * @property float|null $unit_weight_kg
 * @property int|null $media_id
 * 
 * @property Medium|null $media
 * @property Product|null $product
 * @property SalesOrder|null $sales_order
 * @property Collection|InspectionReport[] $inspection_reports_sales_order_line
 * @property Collection|ProductPricing[] $product_pricings_sales_order_line
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
		'units_per_case' => 'int',
		'inner_qty' => 'int',
		'case_weight_kg' => 'float',
		'height_m' => 'float',
		'length_m' => 'float',
		'width_m' => 'float',
		'unit_price' => 'float',
		'unit_price_gbp' => 'float',
		'total' => 'float',
		'total_gbp' => 'float',
		'total_cubic_m' => 'float',
		'unit_width_m' => 'float',
		'unit_length_m' => 'float',
		'unit_height_m' => 'float',
		'unit_weight_kg' => 'float',
		'media_id' => 'int'
	];

	public function media()
	{
		return $this->belongsTo(Medium::class, 'media_id');
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function sales_order()
	{
		return $this->belongsTo(SalesOrder::class);
	}

	public function inspection_reports_sales_order_line()
	{
		return $this->hasMany(InspectionReport::class);
	}

	public function product_pricings_sales_order_line()
	{
		return $this->hasMany(ProductPricing::class);
	}

	public function consignments()
	{
		return $this->belongsToMany(Consignment::class, 'sales_order_lines_consignments')
					->withPivot('qty_shipped', 'shipping_cost_gbp', 'shipping_cost_gbp_per_unit', 'est_landed_cost_per_unit', 'est_landed_cost_per_unit_gbp');
	}
}
