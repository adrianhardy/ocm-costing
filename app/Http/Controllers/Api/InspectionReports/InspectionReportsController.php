<?php

namespace App\Http\Controllers\Api\InspectionReports;

use App\Http\Controllers\Controller;
use App\Models\InspectionReport;
use App\Transformers\InspectionReportTransformer;
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
        return response()->json(
            fractal($inspection->fresh(), new InspectionReportTransformer())
        );
    }

}
