<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Models\ProductPricing;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreatePricingFromOrderLinesController extends Controller
{
    public function store(Request $request)
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

            /** @var SalesOrderLine $line */
            $line = SalesOrderLine::query()->findOrFail($data['id']);

            ProductPricing::query()
                ->whereNull('superseded_at')
                ->whereNull('approved_at')
                ->where('product_id', $line->product->id)
                ->get()
                ->each(function(ProductPricing $superseded) {
                    $superseded->superseded_at = Carbon::now();
                    $superseded->save();
                });

            $costingData = $line->worst_estimated_landed_cost_data;

            $suggestedPrice = $costingData['est_landed_cost_per_unit_gbp'] * setting('suggested_price_multiplier');
            $commission = $suggestedPrice * (1/setting('default_marketplace_commission_pc'));
            $grossCost = $costingData['est_landed_cost_per_unit_gbp'] + $commission;

            $pricing = new ProductPricing();
            $pricing->product()->associate($line->product);
            $pricing->fill([
                'est_landed_cost_per_unit_gbp' => $costingData['est_landed_cost_per_unit_gbp'],
                'suggested_unit_price_gbp' => $costingData['est_landed_cost_per_unit_gbp'] * setting('suggested_price_multiplier'),
                'unit_price_gbp' => $suggestedPrice,
                'marketplace_commission_pc' => setting('default_marketplace_commission_pc'),
                'gross_unit_cost_gbp' => $grossCost,
                'margin_pc' => $suggestedPrice / $grossCost
            ]);
            $pricing->save();
        }
        DB::commit();

        return response()->json([], 201);
    }
}
