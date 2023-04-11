<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Invoice;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceLine
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $invoice_id
 * @property int $sales_order_line_id
 * @property float $unit_price
 * @property int $qty
 * @property float $sub_total
 * 
 * @property Invoice $invoice
 * @property SalesOrderLine $sales_order_line
 *
 * @package App\Models\Base
 */
class InvoiceLine extends Model
{
	protected $table = 'invoice_lines';

	protected $casts = [
		'invoice_id' => 'int',
		'sales_order_line_id' => 'int',
		'unit_price' => 'float',
		'qty' => 'int',
		'sub_total' => 'float'
	];

	public function invoice()
	{
		return $this->belongsTo(Invoice::class);
	}

	public function sales_order_line()
	{
		return $this->belongsTo(SalesOrderLine::class);
	}
}
