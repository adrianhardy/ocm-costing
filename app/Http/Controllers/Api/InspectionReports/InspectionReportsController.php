<?php

namespace App\Http\Controllers\Api\InspectionReports;

use App\Events\ProductInspectionApproved;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\InspectionReport;
use App\Models\Repositories\InspectionReportRepository;
use App\Models\User;
use App\Transformers\InspectionReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InspectionReportsController extends Controller
{
    public function show(InspectionReport $inspection)
    {
        return response()->json(
            fractal($inspection, new InspectionReportTransformer())
        );
    }

    /**
     * @param Request $request
     * @param InspectionReport $inspection
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, InspectionReport $inspection)
    {

        $request->validate([
            'inner_barcode_comments' => 'sometimes|max:512',
            'outer_barcode_comments' => 'sometimes|max:512',
            'product_description_comments' => 'sometimes|max:512',
            'units_per_case_comments' => 'sometimes|max:512',
            'inner_qty_comments' => 'sometimes|max:512',
            'case_weight_kg_comments' => 'sometimes|max:512',
            'unit_weight_kg_comments' => 'sometimes|max|512',
            'case_dimensions_comments' => 'sometimes|max:512',
            'unit_dimensions_comments' => 'sometimes|max:512',
            'packaging_comments' => 'sometimes|max:512',
            'instructions_comments' => 'sometimes|max:512',
            'product_test_comments' => 'sometimes|max:512',
            'appearance_test_comments' => 'sometimes|max:512'
        ]);


        $inspection->fill($request->only(
            'inner_barcode_comments',
            'outer_barcode_comments',
            'product_description_comments',
            'packing instructions_comments',
            'units_per_case_comments',
            'inner_qty_comments',
            'case_weight_kg_comments',
            'unit_weight_kg_comments',
            'case_dimensions_comments',
            'unit_dimensions_comments',
            'packaging_comments',
            'is_unit_size_ok',
            'is_unit_weight_ok',
            'is_case_size_ok',
            'is_case_weight_ok',
            'is_packaging_ok',
            'is_instructions_ok',
            'is_product_test_run_ok',
            'is_appearance_ok',
            'is_inner_barcode_ok',
            'is_outer_barcode_ok',
            'instructions_comments',
            'product_test_comments',
            'appearance_comments',
            'comments'
        ));

        if($request->input('status') == 'rejected') {
            $inspection->completed_at = Carbon::now();
            $inspection->approver()->associate(\Auth::user());
            $inspection->setStatus('rejected');
        } elseif($request->input('status') == 'approved') {
            $inspection->setStatus('approved');
            $inspection->approver()->associate(\Auth::user());
            $inspection->completed_at = Carbon::now();
            event(new ProductInspectionApproved($inspection));
        }

        $inspection->save();


        return response()->json(
            fractal($inspection->fresh(), new InspectionReportTransformer())
        );
    }

    public function index(InspectionReportRepository $reportRepository)
    {
        $paginator = $reportRepository->paginate();

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new InspectionReportTransformer()
        ));
    }

}
