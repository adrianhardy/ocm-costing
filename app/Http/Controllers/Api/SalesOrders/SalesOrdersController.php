<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Repositories\SalesOrderRepository;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Transformers\SalesOrderTransformer;
use Illuminate\Http\Request;

class SalesOrdersController extends Controller
{
    public function index(SalesOrderRepository $salesOrderRepository)
    {
        $paginator = $salesOrderRepository->paginate();
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new SalesOrderTransformer
        ));
    }

    /**
     * @param SalesOrder $sales_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(SalesOrder $sales_order)
    {
        return response()->json(fractal(
            $sales_order, new SalesOrderTransformer
        ));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'reference' => 'required|min:1|max:200',
            'currency.id' => 'required|exists:currencies,id'
        ]);
        $so = new SalesOrder(
            $request->only('reference')
        );
        $so->currency()->associate(Currency::find($request->input('currency.id')));
        $so->supplier()->associate(Supplier::find($request->input('supplier.id')));
        $so->fx_rate = $so->currency->default_fx_to_gbp;
        $so->save();
        return response()->json(fractal(
            $so, new SalesOrderTransformer
        ));
    }

    /**
     * @param Request $request
     * @param SalesOrder $sales_order
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, SalesOrder $sales_order)
    {
        $sales_order->fill($request->only([
            'reference', 'fx_rate'
        ]));

        return response()->json(fractal(
            $sales_order->fresh(), new SalesOrderTransformer
        ));
    }
}
