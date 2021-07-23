<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QuoteLine
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $product_id
 * @property int $quote_id
 * @property string|null $packing_instructions
 * @property string|null $barcode
 * @property float|null $unit_price
 * @property int|null $case_qty
 * @property int|null $case_inner_qty
 * @property int|null $total_unit_qty
 * @property float|null $case_volume_cu_m
 * @property float|null $case_width_m
 * @property float|null $case_height_m
 * @property float|null $case_depth_m
 * 
 * @property Product $product
 * @property Quote $quote
 *
 * @package App\Models\Base
 */
class QuoteLine extends Model
{
	protected $table = 'quote_lines';

	protected $casts = [
		'product_id' => 'int',
		'quote_id' => 'int',
		'unit_price' => 'float',
		'case_qty' => 'int',
		'case_inner_qty' => 'int',
		'total_unit_qty' => 'int',
		'case_volume_cu_m' => 'float',
		'case_width_m' => 'float',
		'case_height_m' => 'float',
		'case_depth_m' => 'float'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function quote()
	{
		return $this->belongsTo(Quote::class);
	}
}
