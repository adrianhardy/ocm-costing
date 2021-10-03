<?php

namespace App\Models;

use App\Models\Base\InspectionReport as BaseInspectionReport;
use Spatie\ModelStatus\HasStatuses;

class InspectionReport extends BaseInspectionReport
{
    use HasStatuses;


    const STATUS_PENDING = 'pending';

	protected $fillable = [
		'sales_order_line_id',
		'consignment_id',
        'unit_height_cm',
        'unit_weight_cm',
        'unit_length_cm',
        'case_height_cm',
        'case_weight_cm',
        'case_length_cm',
        'unit_height_m',
        'unit_weight_m',
        'unit_length_m',
        'case_height_m',
        'case_weight_m',
        'case_length_m',
        'is_unit_size_ok',
        'is_case_size_ok',
        'dimensions_comments',
        'is_packaging_ok',
        'packaging_comments',
        'is_instructions_ok',
        'instructions_comments',
        'is_product_test_run_ok',
        'product_test_comments',
        'is_appearance_ok',
        'appearance_comments',
        'is_barcode_ok',
        'barcode_comments',
        'is_barcode_inner_okay',
        'barcode_inner_comments',
        'barcode_outer',
        'barcode_outer_comments',
        'comments',
        'approved_at',
		'approver_id'
	];
}
