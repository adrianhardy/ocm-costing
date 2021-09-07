<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Consignment
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $reference
 * @property float|null $est_cost_cuft_gbp
 * @property Carbon|null $departed_at
 * @property Carbon|null $docked_at
 * 
 * @property Collection|SalesOrderLine[] $sales_order_lines
 *
 * @package App\Models\Base
 */
class Consignment extends Model
{
	protected $table = 'consignments';

	protected $casts = [
		'est_cost_cuft_gbp' => 'float'
	];

	protected $dates = [
		'departed_at',
		'docked_at'
	];

	public function sales_order_lines()
	{
		return $this->belongsToMany(SalesOrderLine::class, 'sales_order_lines_consignments')
					->withPivot('qty_shipped', 'goods_cost', 'goods_cost_gbp', 'shipping_cost_gbp', 'shipping_cost_gbp_per_unit', 'invoice_amt', 'est_landed_cost_per_unit', 'est_landed_cost_per_unit_gbp');
	}
}
