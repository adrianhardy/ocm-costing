<?php

namespace App\Http\Controllers\InspectionReports;

use App\Http\Controllers\Controller;
use App\Models\InspectionReport;
use Illuminate\Http\Request;

class InspectionReportsController extends Controller
{
    public function edit(InspectionReport $inspection)
    {
        return response()->view('inspection.edit', [
            'inspection' => $inspection
        ]);
    }

}
