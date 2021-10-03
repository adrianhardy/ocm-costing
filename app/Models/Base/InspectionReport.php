<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Consignment;
use App\Models\SalesOrderLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InspectionReport
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $sales_order_line_id
 * @property int $consignment_id
 * @property float $unit_height_m
 * @property float $unit_weight_m
 * @property float $unit_length_m
 * @property float $case_height_m
 * @property float $case_weight_m
 * @property float $case_length_m
 * @property bool|null $is_unit_size_ok
 * @property bool|null $is_case_size_ok
 * @property string|null $dimensions_comments
 * @property bool|null $is_packaging_ok
 * @property string|null $packaging_comments
 * @property string|null $is_instructions_ok
 * @property string|null $instructions_comments
 * @property string|null $is_product_test_run_ok
 * @property string|null $product_test_comments
 * @property string|null $is_appearance_ok
 * @property string|null $appearance_comments
 * @property string|null $is_barcode_ok
 * @property string|null $barcode_comments
 * @property string|null $is_barcode_inner_okay
 * @property string|null $barcode_inner_comments
 * @property string|null $barcode_outer
 * @property string|null $barcode_outer_comments
 * @property string|null $comments
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * 
 * @property User|null $approver
 * @property Consignment $consignment
 * @property SalesOrderLine $sales_order_line
 *
 * @package App\Models\Base
 */
class InspectionReport extends Model
{
	protected $table = 'inspection_reports';

	protected $casts = [
		'sales_order_line_id' => 'int',
		'consignment_id' => 'int',
		'unit_height_m' => 'float',
		'unit_weight_m' => 'float',
		'unit_length_m' => 'float',
		'case_height_m' => 'float',
		'case_weight_m' => 'float',
		'case_length_m' => 'float',
		'is_unit_size_ok' => 'bool',
		'is_case_size_ok' => 'bool',
		'is_packaging_ok' => 'bool',
		'approver_id' => 'int'
	];

	protected $dates = [
		'approved_at'
	];

	public function approver()
	{
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function consignment()
	{
		return $this->belongsTo(Consignment::class);
	}

	public function sales_order_line()
	{
		return $this->belongsTo(SalesOrderLine::class);
	}
}
