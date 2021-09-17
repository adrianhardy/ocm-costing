<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\ProductPricing;
use App\Models\Repositories\ProductPricingRepository;
use App\Transformers\ProductPricingTransformer;
use Illuminate\Http\Request;

class ProductPricingHistoryController extends Controller
{


    /*
     * I know that the name of this controller is a bit of a mouthful,
     * but I needed a controller that also posted up the ID of the pricing.
     */
    public function index(ProductPricing $pricing, ProductPricingRepository $pricingRepository){
        $paginator = $pricingRepository->whereNotNull('approved_at')
            ->where('product_id', $pricing->product_id)
            ->paginate();

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new ProductPricingTransformer()
        ));
    }
}
