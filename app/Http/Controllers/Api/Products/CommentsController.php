<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    /**
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Product $product)
    {
        $product->comment($request->input('comment'));
        return response()->json(
            fractal($product->fresh(), new ProductTransformer())
        );
    }
}
