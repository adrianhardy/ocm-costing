<?php

namespace App\Http\Controllers\Api\Uk;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderAcknowledgementLinesController extends Controller
{
    public function store(Request $request, SalesOrder $orderAcknowledgement){
        $this->authorize('view', $orderAcknowledgement);

        $line = new SalesOrderLine();
        $orderAcknowledgement->lines()->save($line);
        return response()->json(
            fractal($orderAcknowledgement->fresh(), new SalesOrderTransformer())
        );
    }


    public function update(Request $request, SalesOrder $orderAcknowledgement, SalesOrderLine $line){
        $this->authorize('view', $orderAcknowledgement);
        $line->fill($request->only([
            'name',
            'unit_price',
            'units_requested',
            'article_number',
            'item_number',
            'height_cm',
            'length_cm',
            'width_cm',
            'unit_width_cm',
            'unit_length_cm',
            'unit_height_cm',
            'units_per_case',
            'barcode',
            'inner_qty',
            'oa_eta',
            'oa_reorder_number',
            'oa_primary_sku'
        ]));

        if ($request->input('product.id') && $request->input('product.id') != $line->product_id) {
            /** @var Product $product */
            $product = Product::findOrFail($request->input('product.id'));

            $line->fill([
                'name'                  => '' . $product->name,
                'item_number'           => '' . $product->item_number,
                'article_number'        => '' . $product->article_number,
                'unit_price'            => $product->unit_price_gbp > 0?$product->unit_price_gbp:$product->unit_price,
                'primary_sku'           => $product->primary_sku
            ]);
            $line->product()->associate($product);
        }

        if (!$orderAcknowledgement->is_finalised && $line->product) {
            if ($line->units_per_case < 1) {
                $cases = 0;
            } else {
                $cases = intval(ceil($line->units_requested / $line->units_per_case));
            }
            $line->total_cubic_m = $cases * ($line->width_m * $line->length_m * $line->height_m);
        }

        $line->total = $line->units_requested * $line->unit_price;
        $line->total_gbp = $line->total * $orderAcknowledgement->fx_rate;
        $line->unit_price_gbp = $line->unit_price * $orderAcknowledgement->fx_rate;
        $line->save();

        $orderAcknowledgement->touch();

        return response()->json(
            fractal($orderAcknowledgement->fresh(), new SalesOrderTransformer())
        );
    }


    public function destroy(SalesOrder $orderAcknowledgement, SalesOrderLine $line)
    {
        $this->authorize('view', $orderAcknowledgement);
        abort_unless ($line->sales_order_id == $orderAcknowledgement->id, 400);
        $line->delete();
        $orderAcknowledgement->touch();
        return response()->json(
            fractal($orderAcknowledgement->fresh(), new SalesOrderTransformer())
        );
    }



}
