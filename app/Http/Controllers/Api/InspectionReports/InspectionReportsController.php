<?php

namespace App\Http\Controllers\Api\InspectionReports;

use App\Events\ProductInspectionApproved;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\InspectionReport;
use App\Models\Repositories\InspectionReportRepository;
use App\Models\User;
use App\Services\RafService;
use App\Transformers\InspectionReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function update(Request $request, InspectionReport $inspection, RafService $rafService)
    {

        $request->validate([
            'inner_barcode_comments' => 'sometimes|max:512',
            'outer_barcode_comments' => 'sometimes|max:512',
            'product_description_comments' => 'sometimes|max:512',
            'units_per_case_comments' => 'sometimes|max:512',
            'inner_qty_comments' => 'sometimes|max:512',
            'case_weight_kg_comments' => 'sometimes|max:512',
            'unit_weight_kg_comments' => 'sometimes|max:512',
            'case_dimensions_comments' => 'sometimes|max:512',
            'unit_dimensions_comments' => 'sometimes|max:512',
            'packaging_comments' => 'sometimes|max:512',
            'instructions_comments' => 'sometimes|max:512',
            'product_test_comments' => 'sometimes|max:512',
            'appearance_test_comments' => 'sometimes|max:512',
            'status' => 'required'
        ]);

        DB::beginTransaction();

        $inspection->fill($request->only(
            'unit_length_m',
            'unit_height_m',
            'unit_width_m',
            'unit_weight_kg',
            'case_length_m',
            'case_width_m',
            'case_height_m',
            'case_weight_kg',
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
            'comments',
            'outer_barcode'
        ));

        if($request->input('status') == 'rejected') {
            $inspection->completed_at = Carbon::now();
            $inspection->approver()->associate(\Auth::user());
            $inspection->setStatus('rejected');
        } elseif($request->input('status') == 'approved') {
            $inspection->setStatus('approved');
            $inspection->approver()->associate(\Auth::user());
            $inspection->completed_at = Carbon::now();
            //saving here so that the value put up into rafservice are accurate
            $inspection->save();
            try{
                $rafService->updateProductForced($inspection);
            } catch(\Exception $ex){
                //
            }
            event(new ProductInspectionApproved($inspection));
        }

        $inspection->save();

        DB::commit();
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
