<?php

namespace App\Http\Controllers\Api\SalesOrders;

use anlutro\LaravelSettings\ServiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Mail\SupplierSalesOrderFinalised;
use App\Models\Currency;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Repositories\SalesOrderRepository;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\Supplier;
use App\Models\User;
use App\Services\RafService;
use App\Transformers\SalesOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

        if (\Auth::user()->hasAnyRole(['supplier'])) {
            $paginator->where('supplier_id', \Auth::user()->supplier_id);
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
        $isSupplier = \Auth::user()->hasAnyRole(['supplier']);
        if($request->input('products_ids')){
            $products = Product::query()->findMany($request->input('products_ids'));
            foreach($products as $product) {
                foreach($product->factories as $factory) {
                    abort_if($factory->id !== $request->input('factory.id'), '400', 'The selected products do not belong to the selected factory.');
                }
            }
        }

        $this->validate($request, [
            'reference' => 'required|min:1|max:200',
            'currency.id' => 'required|exists:currencies,id',
            'supplier.id' => ($isSupplier?'nullable':'required') . '|exists:suppliers,id'
        ]);

        DB::beginTransaction();
        $so = new SalesOrder(
            $request->only('reference')
        );
        $so->currency()->associate(Currency::find($request->input('currency.id')));

        if ($isSupplier) {
            $so->supplier()->associate(\Auth::user()->supplier);
        } else {
            $so->supplier()->associate(Supplier::find($request->input('supplier.id')));
        }

        $so->fx_rate = $so->currency->default_fx_to_gbp;
        $so->save();

        if($request->input('products_ids' )) {
            foreach($products as $product) {
                $line = new SalesOrderLine();
                $line->fill([
                    'name'                  => '' . $product->name,
                    'item_number'           => '' . $product->item_number,
                    'article_number'        => '' . $product->article_number,
                    'product_description'   => '' . $product->product_description,
                    'packing_instructions'  => '' . $product->packing_instructions,
                    'unit_price'            => $product->unit_price
                ]);
                $line->product()->associate($product);
                $line->sales_order()->associate($so);
                $line->save();
            }
        }


        $so->setStatus(SalesOrder::STATUS_DRAFT);
        DB::commit();


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
                'supplier.id'       => 'required|exists:suppliers,id',
                'factory.id'        => 'required|exists:factories,id',
                'currency.id'       => 'required|exists:currencies,id',
                'status'            => 'nullable|sometimes|in:' . implode(',', array_keys(SalesOrder::STATUSES)),
                'sales_order_notes' => 'min:0|max:2000',
                'shipping_cost_per_cubic_foot'  => 'nullable|numeric|min:0',
                'sales_order_fx_rate'           => 'required|min:0|numeric'
            ], [], [
                'currency.id'           => 'currency',
                'supplier.id'           => 'supplier',
                'factory.id'            => 'factory',
                'sales_order_fx_rate'   => 'FX (Foreign Exchange Rate)'
            ]);
            $currency = Currency::query()->findOrFail($request->input('currency.id'));
            $supplier = Supplier::query()->findOrFail($request->input('supplier.id'));
            $factory = Factory::query()->findOrFail($request->input('factory.id'));
            $sales_order->currency()->associate($currency);
            $sales_order->supplier()->associate($supplier);
            $sales_order->factory()->associate($factory);
            $sales_order->sales_order_fx_rate = $request->input('sales_order_fx_rate');

            $sales_order->fill($request->only([
                'reference', 'sales_order_notes', 'supplier_reference'
            ]));

            if ($request->input('shipping_cost_per_cubic_foot')) {
                $sales_order->shipping_cost_per_cubic_foot = $request->input('shipping_cost_per_cubic_foot');
            }

            if ($request->input('status')) {
                $sales_order->setStatus($request->input('status'));
            }
        }

        if ($request->input('finalised')) {
            if (\Auth::user()->is_supplier) {
                $sales_order->supplier_finalised_at = Carbon::now();
                // Send an email to every super to say that the document has
                // been finalised by the supplier:
                foreach (User::query()->role(['super']) as $super) {
                    $mail = new SupplierSalesOrderFinalised(
                        $sales_order,
                        \Auth::user()
                    );
                    $mail->to($super->email, $super->name);
                    Mail::send($mail);
                }
            } else {
                $sales_order->finalised_at = Carbon::now();
                foreach($sales_order->lines as $line) {
                    try {
                        // $rafService->createProduct($line->product);
                    } catch (\Exception $ex) {
                        // log exception here
                    }
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
