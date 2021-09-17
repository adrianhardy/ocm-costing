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
            'unit_price_gbp' => 'required',
            'marketplace_fee' => 'required'
        ]);

        $pricing->fill($request->only([
            'unit_price_gbp',
            'marketplace_fee'
        ]));

        if($request->input(['submitted'])){
            $pricing->approved_at = Carbon::now();
        }

        if($pricing->unit_price_gbp && $pricing->marketplace_fee){
            $pricing->profit = $pricing->unit_price_gbp;
            $pricing->profit = $pricing->profit - ($pricing->sale_price * $pricing->marketplace_commission_pc / 100);
            $pricing->profit = $pricing->profit - ($pricing->sale_price * $pricing->margin_pc / 100);
            $pricing->profit = $pricing->profit - ($pricing->sale_price * 0.15);
            $pricing->profit = $pricing->profit - $pricing->est_landed_cost_per_unit_gbp;
            $pricing->profit = $pricing->profit - $pricing->marketplace_fee;
        }

        $pricing->save();

        return fractal($pricing->fresh(), new ProductPricingTransformer());
    }
}
