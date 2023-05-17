<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Repositories\SalesOrderLineRepository;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderLineTransformer;
use App\Transformers\SalesOrderTransformer;
use Illuminate\Http\Request;

class LinesController extends Controller
{
    public function index(Request $request, SalesOrderLineRepository $repository)
    {
        $paginator = $repository->whereHas('sales_order', function($query){
            return $query->where('is_order_acknowledgement', false);
        });

        if($request->input('sales_order_id')) {
            $paginator = $paginator->where('sales_order_id', $request->input('sales_order_id'));
        }

        if($request->input('product_id')){
            $paginator = $paginator->where('product_id', $request->input('product_id'));
        }

        $paginator = $paginator->paginate($request->input('per_page', 25));


        return response()->json(new SerializeLengthAwarePaginator($paginator, new SalesOrderLineTransformer()));
    }


    /**
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);

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
        $this->authorize('view', $salesOrder);
        $line->fill($request->only([
            'packing_instructions',
            'product_description',
            'name',
            'unit_price',
            'units_requested',
            'total_cubic_ft',
            'article_number',
            'item_number',
            'case_weight_kg',
            'unit_weight_kg',
            'height_cm',
            'length_cm',
            'width_cm',
            'unit_width_cm',
            'unit_length_cm',
            'unit_height_cm',
            'units_per_case',
            'barcode',
            'inner_qty'
        ]));

        if ($request->input('product.id') && $request->input('product.id') != $line->product_id) {
            /** @var Product $product */
            $product = Product::findOrFail($request->input('product.id'));

            if ($salesOrder->factory && !$product->factories->contains($salesOrder->factory)) {
                abort(400, 'You can only add products whose factories contain ' . $salesOrder->factory->name);
            }

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

        if (!$salesOrder->is_finalised && $line->product) {
            if ($line->units_per_case < 1) {
                $cases = 0;
            } else {
                $cases = intval(ceil($line->units_requested / $line->units_per_case));
            }
            $line->total_cubic_m = $cases * ($line->width_m * $line->length_m * $line->height_m);
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
        $this->authorize('view', $salesOrder);
        abort_unless ($line->sales_order_id == $salesOrder->id, 400);
        $line->delete();
        $salesOrder->touch();
        return response()->json(
            fractal($salesOrder->fresh(), new SalesOrderTransformer())
        );
    }
}
