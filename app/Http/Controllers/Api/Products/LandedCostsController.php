<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductLandedCost;
use App\Transformers\ProductLandedCostTransformer;
use Illuminate\Http\Request;

class LandedCostsController extends Controller
{
    /**
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index (Product $product, Request $request)
    {
        return response()->json([
            'items' => fractal($product->landed_costs, new ProductLandedCostTransformer)
        ]);
    }

    public function store(Product $product, Request $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d',
            'shipping_cost_per_cuft' => 'required|numeric|min:0',
            'currency.id' => 'required|exists:currencies,id',
            'fx_rate' => 'required|numeric|min:0',
            'import_duty_pc' => 'required|numeric|min:0',
            'commission_rate_pc' => 'required|numeric|min:0',
            'landed_cost_per_unit_gbp' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'length_cm' => 'required|numeric|min:0',
            'width_cm' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
        ]);
        $currency = Currency::query()->findOrFail($request->input('currency.id'));
        $cost = new ProductLandedCost($request->only([
            'units_per_case',
            'date',
            'fx_rate',
            'shipping_cost_per_cuft',
            'import_duty_pc',
            'commission_rate_pc',
            'landed_cost_per_unit_gbp',
            'unit_price',
            'length_cm',
            'width_cm',
            'height_cm'
        ]));
        $cost->landed_cost_per_unit = $cost->landed_cost_per_unit_gbp * $cost->fx_rate;
        $cost->product()->associate($product);
        $cost->currency()->associate($currency);
        $cost->save();
    }
}
