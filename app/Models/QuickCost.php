<?php

namespace App\Models;

use App\Models\Base\QuickCost as BaseQuickCost;

class QuickCost extends BaseQuickCost
{
	protected $fillable = [
		'reference',
		'user_id',
		'exchange_rate',
		'unit_price',
		'units_per_case',
		'case_width_m',
		'case_height_m',
		'case_depth_m',
		'cost_to_ship_cu_m',
		'commission_rate',
		'import_duty_rate'
	];
}
