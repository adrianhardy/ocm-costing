<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderTransformer;
use Illuminate\Http\Request;

class LinesController extends Controller
{
    /**
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SalesOrder $salesOrder)
    {
        $line = new SalesOrderLine();
        $salesOrder->lines()->save($line);
        return response()->json(
            fractal($salesOrder->fresh(), new SalesOrderTransformer())
        );
    }

    /**
     * @param SalesOrder $salesOrder
     * @param SalesOrderLine $salesOrderLine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, SalesOrder $salesOrder, SalesOrderLine $line)
    {
        $line->fill($request->only([
            'packing_instructions',
            'product_description',
            'name',
            'unit_price',
            'units_requested',
            'total_cubic_ft'
        ]));

        if ($request->input('product.id') && $request->input('product.id') != $line->product_id) {
            $product = Product::findOrFail($request->input('product.id'));
            $line->fill([
                'name'                  => '' . $product->name,
                'item_number'           => '' . $product->item_number,
                'article_number'        => '' . $product->article_number,
                'product_description'   => '' . $product->product_description,
                'packing_instructions'  => '' . $product->packing_instructions,
                'unit_price'            => $product->unit_price
            ]);
            $line->product()->associate($product);
        }

        if (!$salesOrder->is_finalised &&
            $line->isDirty('units_requested') &&
            $line->product->case_cubic_m > 0 &&
            $line->product->units_per_case > 0
        ) {
            $cases = intval(ceil($line->units_requested / $line->product->units_per_case));
            $line->total_cubic_m = $cases * $line->product->case_cubic_m;
        }

        $line->total = $line->units_requested * $line->unit_price;
        $line->total_gbp = $line->total * $salesOrder->fx_rate;
        $line->unit_price_gbp = $line->unit_price * $salesOrder->fx_rate;
        $line->save();

        $salesOrder->touch();

        return response()->json(
            fractal($salesOrder->fresh(), new SalesOrderTransformer())
        );
    }

    /**
     * @param SalesOrder $salesOrder
     * @param SalesOrderLine $line
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(SalesOrder $salesOrder, SalesOrderLine $line)
    {
        abort_unless ($line->sales_order_id == $salesOrder->id, 400);
        $line->delete();
        $salesOrder->touch();
        return response()->json(
            fractal($salesOrder->fresh(), new SalesOrderTransformer())
        );
    }
}
