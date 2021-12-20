<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->view('products.index');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->view('products.create', [
            'exchange_rate_usd' => Currency::query()->where('code', 'USD')->value('default_fx_to_gbp')
        ]);
    }

    /**
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return response()->view(
            'products.edit', [
                'product' => $product,
                'shipping_cost_per_cubic_foot' => setting('shipping_cost_per_cubic_foot'),
                'exchange_rate_usd' => Currency::query()->where('code', 'USD')->value('default_fx_to_gbp')
            ]);
    }
}
