<?php

namespace App\Models;

use App\Models\Base\SalesOrderLine as BaseSalesOrderLine;

class SalesOrderLine extends BaseSalesOrderLine
{
	protected $fillable = [
		'sales_order_id',
		'item_number',
		'article_number',
		'product_id',
		'units_requested',
        'packing_instructions',
        'product_description',
		'unit_price',
		'unit_price_gbp',
		'total',
		'total_gbp',
	];
}
