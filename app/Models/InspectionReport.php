<?php

namespace App\Models;

use App\Models\Base\InspectionReport as BaseInspectionReport;
use App\Models\Traits\ProductDimensionMutators;
use Spatie\ModelStatus\HasStatuses;

class InspectionReport extends BaseInspectionReport
{
    use HasStatuses;
    use ProductDimensionMutators;

    const STATUS_PENDING = 'pending';

    const STATUSES = [
        self::STATUS_PENDING => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected'
    ];

	protected $fillable = [
		'sales_order_line_id',
		'consignment_id',
		'product_description',
		'packing_instructions',
		'units_per_case',
		'inner_qty',
		'case_weight_kg',
		'unit_weight_kg',
		'inner_barcode',
		'outer_barcode',
		'inner_barcode_comments',
		'outer_barcode_comments',
		'product_description_comments',
		'packing_instructions_comments',
		'units_per_case_comments',
		'inner_qty_comments',
		'case_weight_kg_comments',
		'unit_weight_kg_comments',
        'unit_length_m',
		'unit_width_m',
        'unit_height_m',
        'case_length_m',
		'case_width_m',
        'case_height_m',
        'unit_length_cm',
        'unit_width_cm',
        'unit_height_cm',
        'case_length_cm',
        'case_width_cm',
        'case_height_cm',
		'is_unit_size_ok',
        'is_unit_weight_ok',
		'is_case_size_ok',
        'is_case_weight_ok',
		'case_dimensions_comments',
		'unit_dimensions_comments',
		'is_packaging_ok',
		'packaging_comments',
		'is_instructions_ok',
		'is_product_test_run_ok',
		'is_appearance_ok',
		'is_inner_barcode_ok',
		'is_outer_barcode_ok',
		'instructions_comments',
		'product_test_comments',
		'appearance_comments',
		'comments',
		'completed_at',
		'approver_id'
	];

    /**
     * @param SalesOrderLine $line
     * @return InspectionReport
     */
	public static function createFromSalesOrderLine(SalesOrderLine $line)
    {
        $inspection = new self();
        $inspection->fill($line->only([
            'product_description',
            'packing_instructions',
            'units_per_case',
            'inner_qty',
            'case_weight_kg',
            'unit_weight_kg',
            'unit_length_m',
            'unit_width_m',
            'unit_height_m',
        ]));
        $inspection->fill([
            'case_length_m' => $line->length_m * 100,
            'case_width_m' => $line->width_m * 100,
            'case_height_m' => $line->height_m * 100,
            'inner_barcode' => $line->barcode
        ]);
        if (!$inspection->product_description) {
            $inspection->product_description = '';
        }
        $inspection->sales_order_line()->associate($line);
        return $inspection;
    }

    public function getIsCompletedAttribute()
    {
        return $this->completed_at !== null;
    }

    public function setCaseLengthCmAttribute($cm)
    {
        return $this->case_length_m = ($cm * 100);
    }
    public function setCaseHeightCmAttribute($cm)
    {
        return $this->case_height_m = ($cm * 100);
    }
    public function setCaseWidthCmAttribute($cm)
    {
        return $this->case_width_m = ($cm * 100);
    }
    public function getCaseLengthCmAttribute($cm)
    {
        return $this->case_length_m * 100;
    }
    public function getCaseHeightCmAttribute($cm)
    {
        return $this->case_height_m * 100;
    }
    public function getCaseWidthCmAttribute($cm)
    {
        return $this->case_width_m * 100;
    }
}
