<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\SalesOrder;
use App\Transformers\ConsignmentTransformer;
use Illuminate\Http\Request;

class ConsignmentsController extends Controller
{

    /**
     * @param SalesOrder $sales_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SalesOrder $sales_order)
    {
        return response()->json([
            'items' => fractal($sales_order->consignments, new ConsignmentTransformer())
        ]);
    }

    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     */
    public function store(Request $request, SalesOrder $salesOrder)
    {
        $consignment = new Consignment($request->only([
            'invoice_amt',
            'reference'
        ]));
        $consignment->sales_order()->associate($salesOrder);
        $consignment->creator()->associate(\Auth::user());
        $consignment->save();
    }
}
