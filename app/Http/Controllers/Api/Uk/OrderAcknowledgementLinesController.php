<?php

namespace App\Http\Controllers\Api\Uk;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderAcknowledgementLinesController extends Controller
{
    public function store(Request $request, SalesOrder $orderAcknowledgement){
        $this->authorize('view', $orderAcknowledgement);

        $line = new SalesOrderLine();
        $orderAcknowledgement->lines()->save($line);
        $orderAcknowledgement->touch();
        return response()->json(
            fractal($orderAcknowledgement->fresh(), new SalesOrderTransformer())
        );
    }


    public function update(Request $request, SalesOrder $orderAcknowledgement, SalesOrderLine $line)
    {
        $this->authorize('view', $orderAcknowledgement);
        $line->fill($request->only([
            'name',
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
            'oa_reorder_number',
            'oa_primary_sku'
        ]));

        if ($request->input('unit_price') !== null) {
            $line->unit_price = $request->input('unit_price');
            $line->unit_price_gbp = null;
            \Log::warning(__METHOD__ .  " something is setting 'unit_price' on the OA");
        }

        if ($request->input('unit_price_gbp') !== null) {
            $line->unit_price_gbp = $request->input('unit_price_gbp');
            $line->unit_price = null;
        }

        $line->oa_eta = Carbon::parse($request->input('oa_eta'));

        if ($request->input('product.id') && $request->input('product.id') != $line->product_id) {
            /** @var Product $product */
            $product = Product::findOrFail($request->input('product.id'));

            $line->fill([
                'name'                  => '' . $product->name,
                'item_number'           => '' . $product->item_number,
                'article_number'        => '' . $product->article_number,
                // AH : temporarily - remove if we need it pls
                // 'unit_price'            => $product->unit_price,
                'unit_price_gbp'        => $product->unit_price_gbp,
                'oa_primary_sku'        => $product->primary_sku
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

        if ($line->unit_price_gbp && $line->unit_price_gbp > 0) {
            $line->total_gbp = $line->units_requested * $line->unit_price_gbp;
            $line->total = null;
        } else {
            $line->total = $line->units_requested * $line->unit_price;
            $line->total_gbp = $line->total * $orderAcknowledgement->fx_rate;
        }

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
