<?php

namespace App\Models;

use App\Models\Base\InvoiceLine as BaseInvoiceLine;


/**
 * Class InvoiceLine
 * @package App\Models
 *
 * @property-read int $qty_received_excl_me the quantity yet to be received based on the sales order line
 */
class InvoiceLine extends BaseInvoiceLine
{
	protected $fillable = [
		'invoice_id',
		'sales_order_line_id',
		'unit_price',
		'qty',
		'sub_total'
	];

    public function getQtyReceivedExclMeAttribute()
    {
        return InvoiceLine::query()
            ->where('sales_order_line_id', $this->sales_order_line->id)
            ->whereHas('invoice', function($query) {
                return $query->whereNull('deleted_at');
            })
            ->sum('qty') - $this->qty;
    }

}
