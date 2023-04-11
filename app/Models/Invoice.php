<?php

namespace App\Models;

use App\Models\Base\Invoice as BaseInvoice;

/**
 * Class Invoice
 * @package App\Models
 * @property InvoiceLine[] $invoice_lines
 *
 */
class Invoice extends BaseInvoice
{
	protected $fillable = [
		'sales_order_id',
		'creator_id',
		'reference',
		'approved_at',
		'approver_id',
		'sub_total',
		'is_approval_required'
	];

	public function invoice_lines(){
        return $this->invoice_lines_invoice();
    }

}
