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
            $pricing = ProductPricing::query()->where('product_id', $product->id)->exists();

            if($product->est_landed_cost_gbp <= 0) {
                throw new HttpException('500', 'The estimated landed cost must be greater than 0');
            } elseif ($pricing) {
                throw new HttpException('500', 'Product already has pricing');
            }

            $products = [
                $product
            ];
        }

        DB::beginTransaction();

        foreach($products as $product) {
            $pricingService->sendToPricing($product);
        }

        DB::commit();
    }
}
