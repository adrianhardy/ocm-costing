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
        'finalised_at',
        'sent_at',
		'fx_rate',
		'supplier_id'
	];

	public function getStageDescriptionAttribute()
    {
        if ($this->finalised_at) {
            return 'Sales Order';
        }
        return 'Quote';
    }

    public function getIsFinalisedAttribute()
    {
        return $this->finalised_at !== null;
    }

	public function getIsSentAttribute()
    {
        return $this->sent_at !== null;
    }

    public function getSalesOrderNumberAttribute()
    {
        return str_pad($this->id, 8, '0', \STR_PAD_LEFT);
    }

	public function lines()
    {
        return $this->sales_order_lines_sales_order();
    }
}
