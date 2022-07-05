<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ProductPricing;
use App\Models\ProductPricingCurrency;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \App\Services\PricingService;

class CreatePricingFromOrderLinesController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, PricingService $pricing)
    {

        foreach ($request->input('lines') as $data) {
            /** @var SalesOrderLine $line */
            $line = SalesOrderLine::query()->findOrFail($data['id']);
            $costingData = $line->worst_estimated_landed_cost_data;
            if (!$costingData) {
                abort(400, "Line for product {$line->product->article_number} doesn't have any shipping data");
            }
        }


        DB::beginTransaction();
        foreach ($request->input('lines') as $data) {
            $line = SalesOrderLine::query()->findOrFail($data['id']);
            $pricing->sendToPricing($line);
        }
        DB::commit();

        return response()->json([], 201);
    }
}
