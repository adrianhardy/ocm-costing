<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\InvoiceLine;
use App\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $sales_order_id
 * @property int $creator_id
 * @property string $reference
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * @property float|null $sub_total
 * @property bool $is_approval_required
 * @property Carbon|null $invoice_date
 * 
 * @property User|null $approver
 * @property User $creator
 * @property SalesOrder $sales_order
 * @property Collection|InvoiceLine[] $invoice_lines_invoice
 *
 * @package App\Models\Base
 */
class Invoice extends Model
{
	use SoftDeletes;
	protected $table = 'invoices';

	protected $casts = [
		'sales_order_id' => 'int',
		'creator_id' => 'int',
		'approver_id' => 'int',
		'sub_total' => 'float',
		'is_approval_required' => 'bool'
	];

	protected $dates = [
		'approved_at',
		'invoice_date'
	];

	public function approver()
	{
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function sales_order()
	{
		return $this->belongsTo(SalesOrder::class);
	}

	public function invoice_lines_invoice()
	{
		return $this->hasMany(InvoiceLine::class);
	}
}
