<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\ProductPricing;
use App\Models\Repositories\ProductPricingRepository;
use App\Transformers\ProductPricingTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductPricingController extends Controller
{
    public function index(ProductPricingRepository $productPricingRepository)
    {
        // Pricing's to be approved/done:
        $paginator = $productPricingRepository
            ->whereNull('superseded_at')
            ->whereNull('approved_at')
            ->paginate();

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductPricingTransformer()
        ));
    }

    public function show(ProductPricing $pricing){
        return response()->json(fractal($pricing, new ProductPricingTransformer()));

    }

    public function update(Request $request, ProductPricing $pricing)
    {
        $this->validate($request, [
            'unit_price_gbp' => 'required|numeric',
            'marketplace_fee' => 'required|numeric',
            'marketplace_commission_pc' => 'required|numeric',
            'unit_shipping_gbp' => 'required|numeric'
        ]);

        $pricing->fill($request->only([
            'unit_price_gbp',
            'marketplace_fee',
            'marketplace_commission_pc',
            'unit_shipping_gbp'
        ]));

        if($request->input(['submitted'])){
            $pricing->approved_at = Carbon::now();
        }

        $pricing->profit = $pricing->calculated_gross_profit_gbp;
        $pricing->margin_pc = $pricing->calculated_gross_profit_gbp / $pricing->unit_price_gbp;
        $pricing->save();

        return fractal($pricing->fresh(), new ProductPricingTransformer());
    }
}
