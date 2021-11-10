<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
}
