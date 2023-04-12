<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\ProductLandedCost;
use App\Models\ProductPricing;
use App\Models\ProductsFactory;
use App\Models\QuoteLine;
use App\Models\SalesOrderLine;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $item_number
 * @property string $article_number
 * @property string $name
 * @property int $supplier_id
 * @property int|null $units_per_case
 * @property int|null $inner_qty
 * @property float|null $case_weight_kg
 * @property float|null $unit_weight_kg
 * @property float|null $height_m
 * @property float|null $length_m
 * @property float|null $width_m
 * @property float|null $unit_height_m
 * @property float|null $unit_length_m
 * @property float|null $unit_width_m
 * @property float|null $unit_price
 * @property float|null $est_landed_cost_gbp
 * @property string|null $barcode
 * @property string|null $packing_instructions
 * @property string|null $product_description
 * @property string|null $deleted_at
 * @property bool|null $is_excluded_from_commission
 * @property string|null $primary_sku
 * @property string|null $primary_asin
 * @property string|null $additional_skus
 * @property string|null $additional_asins
 * @property string|null $uk_postage_method
 * @property string|null $eu_postage_method
 * @property string|null $row_postage_method
 * @property string $sorted_article_number
 * @property string|null $pricing_notes
 * @property float|null $unit_price_gbp
 * 
 * @property Supplier $supplier
 * @property Collection|ProductLandedCost[] $product_landed_costs_product
 * @property Collection|ProductPricing[] $product_pricings_product
 * @property Collection|ProductsFactory[] $products_factories_product
 * @property Collection|QuoteLine[] $quote_lines_product
 * @property Collection|SalesOrderLine[] $sales_order_lines_product
 *
 * @package App\Models\Base
 */
class Product extends Model
{
	use SoftDeletes;
	protected $table = 'products';

	protected $casts = [
		'supplier_id' => 'int',
		'units_per_case' => 'int',
		'inner_qty' => 'int',
		'case_weight_kg' => 'float',
		'unit_weight_kg' => 'float',
		'height_m' => 'float',
		'length_m' => 'float',
		'width_m' => 'float',
		'unit_height_m' => 'float',
		'unit_length_m' => 'float',
		'unit_width_m' => 'float',
		'unit_price' => 'float',
		'est_landed_cost_gbp' => 'float',
		'is_excluded_from_commission' => 'bool',
		'unit_price_gbp' => 'float'
	];

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function product_landed_costs_product()
	{
		return $this->hasMany(ProductLandedCost::class);
	}

	public function product_pricings_product()
	{
		return $this->hasMany(ProductPricing::class);
	}

	public function products_factories_product()
	{
		return $this->hasMany(ProductsFactory::class);
	}

	public function quote_lines_product()
	{
		return $this->hasMany(QuoteLine::class);
	}

	public function sales_order_lines_product()
	{
		return $this->hasMany(SalesOrderLine::class);
	}
}
