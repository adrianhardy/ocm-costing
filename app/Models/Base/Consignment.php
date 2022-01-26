<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\InspectionReport;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
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
 * @property int|null $sales_order_id
 * @property float|null $invoice_amt
 * @property int|null $creator_id
 * @property string|null $supplier_ref
 * @property string|null $our_order_number
 * @property string|null $vessel_name
 * @property string|null $container_number
 * @property string|null $bl_number
 * @property string|null $shipping_agent_ref
 * 
 * @property User|null $creator
 * @property SalesOrder|null $sales_order
 * @property Collection|InspectionReport[] $inspection_reports_consignment
 * @property Collection|SalesOrderLine[] $sales_order_lines
 *
 * @package App\Models\Base
 */
class Consignment extends Model
{
	protected $table = 'consignments';

	protected $casts = [
		'est_cost_cuft_gbp' => 'float',
		'sales_order_id' => 'int',
		'invoice_amt' => 'float',
		'creator_id' => 'int'
	];

	protected $dates = [
		'departed_at',
		'docked_at'
	];

	public function creator()
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function sales_order()
	{
		return $this->belongsTo(SalesOrder::class);
	}

	public function inspection_reports_consignment()
	{
		return $this->hasMany(InspectionReport::class);
	}

	public function sales_order_lines()
	{
		return $this->belongsToMany(SalesOrderLine::class, 'sales_order_lines_consignments')
					->withPivot('qty_shipped', 'shipping_cost_gbp', 'shipping_cost_gbp_per_unit', 'est_landed_cost_per_unit', 'est_landed_cost_per_unit_gbp');
	}
}
