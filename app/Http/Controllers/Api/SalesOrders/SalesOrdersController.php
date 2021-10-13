<?php

namespace App\Http\Controllers\Api\SalesOrders;

use anlutro\LaravelSettings\ServiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Repositories\SalesOrderRepository;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Services\RafService;
use App\Transformers\SalesOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesOrdersController extends Controller
{
    /**
     * @param SalesOrder $sales_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(SalesOrder $sales_order)
    {
        $this->authorize('view', $sales_order);
        return response()->json(fractal(
            $sales_order, new SalesOrderTransformer
        ));
    }

    /**
     * @param Request $request
     * @param SalesOrderRepository $salesOrderRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(
        Request $request,
        SalesOrderRepository $salesOrderRepository
    ) {
        $paginator = $salesOrderRepository;

        switch($request->query('stage'))
        {
            case 'quote':
                $paginator = $paginator->whereNull('finalised_at')->whereNull('sent_at');
                break;
            case 'finalised':
                $paginator = $paginator->whereNotNull('finalised_at')->whereNull('sent_at');
                break;
            Case 'sent':
                $paginator = $paginator->whereNotNull('finalised_at')->whereNotNull('sent_at');
                break;
        }


        $paginator = $salesOrderRepository->paginate(
            $request->input('per_page', 100)
        );
        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new SalesOrderTransformer
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
    public function update(Request $request, SalesOrder $sales_order, RafService $rafService)
    {
        $this->authorize('view', $sales_order);
        if(!$request->input('finalised')){
            $request->validate([
                'supplier.id' => 'required|exists:suppliers,id',
                'currency.id' => 'required|exists:currencies,id',
                'fx_rate' => 'required|min:0|numeric'
            ], [], [
                'currency.id' => 'currency',
                'supplier.id' => 'supplier',
                'fx_rate' => 'FX (Foreign Exchange Rate)'
            ]);
            $currency = Currency::query()->findOrFail($request->input('currency.id'));
            $supplier = Supplier::query()->findOrFail($request->input('supplier.id'));
            $sales_order->currency()->associate($currency);
            $sales_order->supplier()->associate($supplier);

            $sales_order->fill($request->only([
                'reference', 'fx_rate'
            ]));

        }

        if ($request->input('finalised')) {
            $sales_order->finalised_at = Carbon::now();
            foreach($sales_order->lines as $line) {
                try {
                    $rafService->createProduct($line->product);
                } catch (\Exception $ex) {
                    // log exception here
                }
            }
        }

        if ($request->input('restore')) {
            $sales_order->finalised_at = null;
        }

        $sales_order->save();

        return response()->json(fractal(
            $sales_order->fresh(), new SalesOrderTransformer
        ));
    }
}
