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
 * @property string|null $product_description
 * @property string|null $packing_instructions
 * @property int|null $units_per_case
 * @property int|null $inner_qty
 * @property float|null $case_weight_kg
 * @property float|null $unit_weight_kg
 * @property string|null $inner_barcode
 * @property string|null $outer_barcode
 * @property float|null $unit_length_m
 * @property float|null $unit_width_m
 * @property float|null $unit_height_m
 * @property float|null $case_length_m
 * @property float|null $case_width_m
 * @property float|null $case_height_m
 * @property bool|null $is_unit_size_ok
 * @property bool|null $is_case_size_ok
 * @property bool|null $is_packaging_ok
 * @property bool|null $is_instructions_ok
 * @property bool|null $is_product_test_run_ok
 * @property bool|null $is_appearance_ok
 * @property bool|null $is_inner_barcode_ok
 * @property bool|null $is_outer_barcode_ok
 * @property bool|null $is_unit_weight_ok
 * @property bool|null $is_case_weight_ok
 * @property string|null $inner_barcode_comments
 * @property string|null $outer_barcode_comments
 * @property string|null $product_description_comments
 * @property string|null $packing_instructions_comments
 * @property string|null $units_per_case_comments
 * @property string|null $inner_qty_comments
 * @property string|null $case_weight_kg_comments
 * @property string|null $unit_weight_kg_comments
 * @property string|null $case_dimensions_comments
 * @property string|null $unit_dimensions_comments
 * @property string|null $packaging_comments
 * @property string|null $instructions_comments
 * @property string|null $product_test_comments
 * @property string|null $appearance_comments
 * @property string|null $comments
 * @property Carbon|null $completed_at
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
		'units_per_case' => 'int',
		'inner_qty' => 'int',
		'case_weight_kg' => 'float',
		'unit_weight_kg' => 'float',
		'unit_length_m' => 'float',
		'unit_width_m' => 'float',
		'unit_height_m' => 'float',
		'case_length_m' => 'float',
		'case_width_m' => 'float',
		'case_height_m' => 'float',
		'is_unit_size_ok' => 'bool',
		'is_case_size_ok' => 'bool',
		'is_packaging_ok' => 'bool',
		'is_instructions_ok' => 'bool',
		'is_product_test_run_ok' => 'bool',
		'is_appearance_ok' => 'bool',
		'is_inner_barcode_ok' => 'bool',
		'is_outer_barcode_ok' => 'bool',
		'is_unit_weight_ok' => 'bool',
		'is_case_weight_ok' => 'bool',
		'approver_id' => 'int'
	];

	protected $dates = [
		'completed_at'
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
