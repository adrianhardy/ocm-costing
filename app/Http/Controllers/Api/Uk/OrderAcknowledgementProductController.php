<?php

namespace App\Http\Controllers\Api\Uk;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderLineTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAcknowledgementProductController extends Controller
{
    public function store(Request $request, SalesOrder $orderAcknowledgement, SalesOrderLine $line){
        $this->validate($request, [
            'name' => 'required',
            'article_number' => 'required|max:32',
            'barcode' => 'sometimes',
            'primary_sku' => 'sometimes',
            'length_m' => 'sometimes',
            'width_m' => 'sometimes',
            'height_m' => 'sometimes',
            'unit_length_m' => 'sometimes',
            'unit_width_m' => 'sometimes',
            'unit_height_m' => 'sometimes',
            'unit_price' => 'sometimes',
            'units_per_case' => 'sometimes',
        ]);

        DB::beginTransaction();

        $product = new Product();
        $product->fill($request->only([
            'name',
            'barcode',
            'primary_sku',
            'length_m',
            'width_m',
            'height_m',
            'unit_length_m',
            'unit_width_m',
            'unit_height_m',
            'units_per_case'
        ]));

        $product->primary_sku = $request->input('oa_primary_sku');

        $product->supplier()->associate($orderAcknowledgement->supplier);
        $product->item_number = $request->input('item_number');
        $product->article_number = $request->input('article_number');
        $product->unit_price_gbp = $request->input('unit_price');

        if ($product->article_number && !$product->item_number) {
            $product->item_number = $product->article_number;
        }

        if (!$product->item_number &&
            $request->input('item_number') === null &&
            $product->article_number
        ) {
            $product->item_number = $product->article_number;
        }

        abort_unless($product->item_number, 400, 'No item number has been provided');

        $product->save();


        $line->fill([
            'name'                     => '' . $product->name,
            'item_number'              => '' . $product->item_number,
            'article_number'           => '' . $product->article_number,
            'unit_price'               => $product->unit_price_gbp,
            'oa_primary_sku'           => $product->primary_sku
        ]);

        $line->product()->associate($product);

        $line->save();
        $product->save();

        DB::commit();

        return response()->json(fractal($line, new SalesOrderLineTransformer()));
    }
}
