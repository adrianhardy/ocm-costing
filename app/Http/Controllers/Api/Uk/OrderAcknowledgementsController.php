<?php

namespace App\Http\Controllers\Api\Uk;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Repositories\SalesOrderRepository;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\Supplier;
use App\Transformers\SalesOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;

class OrderAcknowledgementsController extends Controller
{
    public function index(Request $request, SalesOrderRepository $repository){
        $paginator = $repository->pushCriteria(app(RequestCriteria::class));
        // $paginator = $paginator->where('is_order_acknowledgement', true);
        // adding on "SalesOrder::STATUS_CONFIRMED_SALES_ORDER" onto status so that we always
        // get through all of WS's Sales Orders that may be of interest to toni. But, we only
        // do that on "ACTIVE" filter. BUT, all other non active statuses filter out WS' Orders.
        switch($request->input('stage')) {
            case 'draft':
                $paginator = $paginator->where('is_order_acknowledgement', true);
                $paginator = $paginator->currentStatus([SalesOrder::STATUS_DRAFT, SalesOrder::OA_STATUS_UNMATCHED_PRODUCTS]);
                break;
            case 'unmatched_products':
                $paginator = $paginator->where('is_order_acknowledgement', true);
                $paginator = $paginator->currentStatus(SalesOrder::OA_STATUS_UNMATCHED_PRODUCTS);
                break;
            case 'active':
                // include confirmed sales orders
                $paginator = $paginator->currentStatus([SalesOrder::OA_STATUS_ACTIVE, SalesOrder::OA_STATUS_PARTIALLY_RECEIVED, SalesOrder::STATUS_CONFIRMED_SALES_ORDER]);
                break;
            case 'partially_received':
                $paginator = $paginator->where('is_order_acknowledgement', true);
                $paginator = $paginator->currentStatus(SalesOrder::OA_STATUS_PARTIALLY_RECEIVED);
                break;
            case 'fully_received':
                $paginator = $paginator->where('is_order_acknowledgement', true);
                $paginator = $paginator->currentStatus([SalesOrder::OA_STATUS_FULLY_RECEIVED, SalesOrder::OA_STATUS_CLOSED]);
                break;
            default:
                $paginator = $paginator->where('is_order_acknowledgement', true);
                break;
        }


        if($request->input('product_id')) {
            $paginator = $paginator->whereHas('lines', function($query) use ($request) {
                return $query->where('product_id', $request->input('product_id'));
            });
        }

        switch($request->input('stage'))
        {
            case 'deleted':
                $paginator = $paginator->whereNotNull('deleted_at')->withTrashed();
        }


        $paginator = $paginator->paginate($request->input('per_page', 15));

        return response()->json(new SerializeLengthAwarePaginator($paginator, new SalesOrderTransformer));
    }

    public function store(Request $request){

        $this->validate($request, [
            'supplier.id' => 'required',
            'our_reference' => 'required',
            'supplier_reference' => 'required'
        ]);

        DB::beginTransaction();

        $acknowledgement = new SalesOrder([
            'reference' => $request->input('our_reference'),
            'supplier_reference' => $request->input('supplier_reference')
        ]);

        $currency = Currency::query()->where('code', 'GBP')->firstOrFail();

        $acknowledgement->currency()->associate($currency);
        $acknowledgement->supplier()->associate($request->input('supplier.id'));
        $acknowledgement->shipping_cost_per_cubic_foot = 0;
        // we're setting the shipping cost per cubic foot here,
        // as it is required in the database, even if we don't
        // need it for OA's
        $acknowledgement->fx_rate = 1;
        $acknowledgement->is_order_acknowledgement = true;

        $acknowledgement->save();

        DB::commit();
        $acknowledgement->fresh();


        $acknowledgement->setStatus(SalesOrder::STATUS_DRAFT);


        return response()->json(fractal($acknowledgement, new SalesOrderTransformer));
    }

    public function update(Request $request, SalesOrder $orderAcknowledgement){
        $this->authorize('view', $orderAcknowledgement);
        if(!$request->input('finalised') && !$request->input('closed') && !$request->input('reopen')) {
            $this->validate($request, [
                'supplier.id' => 'required',
                'reference' => 'required',
                'supplier_reference' => 'required'
            ]);

            $supplier = Supplier::query()->findOrFail($request->input('supplier.id'));
            $orderAcknowledgement->supplier()->associate($supplier);
            $orderAcknowledgement->fill($request->only(
                'reference', 'supplier_reference'
            ));
        }


        if ($request->input('finalised')) {
            abort_if(
                $orderAcknowledgement->status === SalesOrder::OA_STATUS_UNMATCHED_PRODUCTS,
                400,
                'You cannot finalise a order acknowledgement with unmatched products'
            );
            $orderAcknowledgement->finalised_at = Carbon::now();
        }

        if($request->input('closed')) {
            $orderAcknowledgement->setStatus(SalesOrder::OA_STATUS_CLOSED);
        }

        if($request->input('reopen')) {
            // this will set the status to draft,
            // if the OA has invoices, the observer
            // will set the correct status during save
            $orderAcknowledgement->setStatus(SalesOrder::STATUS_DRAFT);
        }


        DB::beginTransaction();
        if($request->input('restore')) {

            $orderAcknowledgement->save();

            // Decision by cat 19/May/23 - allow orders which have invoices
            // against them to be unfinalised
            $completedInvoices = $orderAcknowledgement->invoices()->count();
            $completedInvoices = 0;
            if( $completedInvoices >= 1) {
                throw new \Exception('You cannot unfinalise an order that has an invoice');
            }

            $orderAcknowledgement->finalised_at = null;
            $orderAcknowledgement->setStatus(SalesOrder::STATUS_DRAFT);
        }

        $orderAcknowledgement->save();

        /*
         * CC 25/10/23 - We only update product prices based on INVOICED amounts, so just because
         * we create an OA and finalise it, nothing happens to the product's price.
         *
        if ($orderAcknowledgement->wasChanged(['finalised_at']) && $orderAcknowledgement->is_finalised) {
            $orderAcknowledgement->fresh()->lines->each(function (SalesOrderLine $line) {
                // only update the product price if we've got a unit price, the unit_price is > 0
                // it doesn't match the current unit_price and there isn't an associated shipping
                // cost with the SO line
            });
        }
        */
        DB::commit();

        return response()->json(fractal($orderAcknowledgement, new SalesOrderTransformer()));
    }


    public function show(Request $request, SalesOrder $orderAcknowledgement){
        return response()->json(fractal($orderAcknowledgement, new SalesOrderTransformer()));
    }

    public function destroy(Request $request, SalesOrder $orderAcknowledgement){
        $orderAcknowledgement->delete();
        return response()->json(fractal($orderAcknowledgement, new SalesOrderTransformer()));
    }

    public function restore(Request $request, $orderAcknowledgement) {
        $orderAcknowledgement = SalesOrder::query()->withTrashed()->where('id', $orderAcknowledgement)->firstOrFail();
        $orderAcknowledgement->restore();
        return response()->json(fractal($orderAcknowledgement, new SalesOrderTransformer()));
    }
}
