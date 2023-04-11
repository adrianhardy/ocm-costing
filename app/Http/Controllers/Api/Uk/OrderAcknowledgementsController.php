<?php

namespace App\Http\Controllers\Api\Uk;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Currency;
use App\Models\Repositories\SalesOrderRepository;
use App\Models\SalesOrder;
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

        $paginator = $paginator->where('is_order_acknowledgement', true);

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

        $acknowledgement->setStatus(SalesOrder::STATUS_DRAFT);


        DB::commit();

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


        if($request->input('restore')) {

            $orderAcknowledgement->save();

            $completedInvoices = $orderAcknowledgement->invoices()->count();


            if($completedInvoices >= 1) {
                throw new \Exception('You cannot unfinalise an order that has an invoice');
            }

            $orderAcknowledgement->finalised_at = null;
            $orderAcknowledgement->setStatus(SalesOrder::STATUS_DRAFT);
        }

        $orderAcknowledgement->save();

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
