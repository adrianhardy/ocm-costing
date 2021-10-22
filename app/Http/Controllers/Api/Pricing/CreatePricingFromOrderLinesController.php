<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Models\Country;
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

            $fr = Country::where('iso_2_code', 'FR')->first();
            $uk = Country::where('iso_2_code', 'GB')->first();
            $de = Country::where('iso_2_code', 'DE')->first();
            $es = Country::where('iso_2_code', 'ES')->first();
            $it = Country::where('iso_2_code', 'IT')->first();
            $nl = Country::where('iso_2_code', 'NL')->first();
            $us = Country::where('iso_2_code', 'US')->first();
            $ca = Country::where('iso_2_code', 'CA')->first();
            $mx = Country::where('iso_2_code', 'MX')->first();

            $pricing = new ProductPricing();
            $pricing->sales_order_line()->associate($line);
            $pricing->product()->associate($line->product);
            $pricing->fill([
                'est_landed_cost_per_unit_gbp' => $costingData['est_landed_cost_per_unit_gbp'],
                'suggested_unit_price_gbp' => $costingData['est_landed_cost_per_unit_gbp'] * setting('suggested_price_multiplier'),
                'unit_price_gbp' => $suggestedPrice,
                'marketplace_commission_pc' => setting('default_marketplace_commission_pc'),
                'gross_unit_cost_gbp' => $grossCost,
                'margin_pc' => $suggestedPrice / $grossCost,
                'fr_vat' => $fr->vat_rate,
                'uk_vat' => $uk->vat_rate,
                'es_vat' => $es->vat_rate,
                'it_vat' => $it->vat_rate,
                'de_vat' => $de->vat_rate,
                'nl_vat' => $nl->vat_rate,
                'mxn_fx_rate' => $mx->currency->default_fx_to_gbp,
                'eur_fx_rate' => $fr->currency->default_fx_to_gbp,
                'usd_fx_rate' => $us->currency->default_fx_to_gbp,
                'cad_fx_rate' => $ca->currency->default_fx_to_gbp,
            ]);
            $pricing->save();
        }
        DB::commit();

        return response()->json([], 201);
    }
}
