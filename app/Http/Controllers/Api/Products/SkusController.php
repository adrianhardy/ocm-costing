<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;

class SkusController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $this->validate($request, [
            'additional_skus' => 'array',
            'additional_asins' => 'array',
            'primary_sku' => 'sometimes|min:0|max:64',
            'primary_asin' => 'sometimes|min:0|max:64',
        ]);

        $product->fill($request->only([
            'primary_sku', 'primary_asin'
        ]));
        $product->additional_skus = $request->input('additional_skus', []);
        $product->additional_asins = $request->input('additional_asins', []);
        $product->save();

        return response()->json(fractal($product, new ProductTransformer()));
    }
}
