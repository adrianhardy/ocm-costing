<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CreatePricingFromProductController extends Controller
{
    /**
     * @param Request $request
     * @param PricingService $pricingService
     * @param Product|null $product
     * @return void
     */
    public function store(
        Request $request,
        PricingService $pricingService,
        Product $product = null
    ) {

        if(!$product) {
            $products = Product::query()
                ->whereDoesntHave('product_pricings_product')
                ->where('est_landed_cost_gbp', '>', 0)
                ->get();
        } else {
            $products = [$product];
        }

        DB::beginTransaction();
        foreach($products as $product) {
            $pricingService->sendToPricing($product);
        }
        DB::commit();
    }
}
