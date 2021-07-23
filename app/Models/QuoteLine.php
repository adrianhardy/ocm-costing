<?php

namespace App\Models;

use App\Models\Base\QuoteLine as BaseQuoteLine;

class QuoteLine extends BaseQuoteLine
{
	protected $fillable = [
		'product_id',
		'quote_id',
		'packing_instructions',
		'barcode',
		'unit_price',
		'case_qty',
		'case_inner_qty',
		'total_unit_qty',
		'case_volume_cu_m',
		'case_width_m',
		'case_height_m',
		'case_depth_m'
	];
}
