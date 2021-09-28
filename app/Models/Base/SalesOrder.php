<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Currency;
use App\Models\SalesOrderAuthentication;
use App\Models\SalesOrderLine;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesOrder
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $reference
 * @property Carbon|null $sent_at
 * @property float|null $goods_cost
 * @property float|null $goods_cost_gbp
 * @property float|null $shipping_cost
 * @property float|null $shipping_cost_gbp
 * @property float|null $total
 * @property float|null $total_gbp
 * @property int|null $currency_id
 * @property float|null $fx_rate
 * @property int $supplier_id
 * @property Carbon|null $finalised_at
 * 
 * @property Currency|null $currency
 * @property Supplier $supplier
 * @property Collection|SalesOrderAuthentication[] $sales_order_authentications_sales_order
 * @property Collection|SalesOrderLine[] $sales_order_lines_sales_order
 *
 * @package App\Models\Base
 */
class SalesOrder extends Model
{
	use SoftDeletes;
	protected $table = 'sales_orders';

	protected $casts = [
		'goods_cost' => 'float',
		'goods_cost_gbp' => 'float',
		'shipping_cost' => 'float',
		'shipping_cost_gbp' => 'float',
		'total' => 'float',
		'total_gbp' => 'float',
		'currency_id' => 'int',
		'fx_rate' => 'float',
		'supplier_id' => 'int'
	];

	protected $dates = [
		'sent_at',
		'finalised_at'
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function sales_order_authentications_sales_order()
	{
		return $this->hasMany(SalesOrderAuthentication::class);
	}

	public function sales_order_lines_sales_order()
	{
		return $this->hasMany(SalesOrderLine::class);
	}
}
