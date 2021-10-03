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
        'sales_order_line', 'consignment'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(InspectionReport $inspection)
    {
        return $inspection->only([
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
        ]);
    }
}
