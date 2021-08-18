<?php

namespace App\Models;

use App\Models\Base\SalesOrder as BaseSalesOrder;

/**
 * Class SalesOrder
 * @package App\Models
 * @property SalesOrderLine[] $lines
 */
class SalesOrder extends BaseSalesOrder
{
	protected $fillable = [
		'sent_at',
        'reference',
		'total',
		'total_gbp',
		'fx_rate',
		'supplier_id'
	];

	public function lines()
    {
        return $this->sales_order_lines_sales_order();
    }
}
