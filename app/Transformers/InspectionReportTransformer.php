<?php

namespace App\Transformers;

use App\Models\InspectionReport;
use League\Fractal\TransformerAbstract;

class InspectionReportTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'sales_order_line', 'consignment', 'approver'
    ];

    public function includeApprover(InspectionReport $inspection)
    {
        if (!$inspection->is_approved) {
            return $this->null();
        }
        return $this->item($inspection->approver, new UserTransformer());
    }

    public function includeConsignment(InspectionReport $inspection)
    {
        return $this->item($inspection->consignment, new ConsignmentTransformer());
    }

    public function includeSalesOrderLine(InspectionReport $inspection)
    {
        return $this->item($inspection->sales_order_line, new SalesOrderLineTransformer());
    }


    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(InspectionReport $inspection)
    {
        return $inspection->only([
            'created_at',
            'status',
            'id',
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
            'is_completed'
        ]);
    }
}
