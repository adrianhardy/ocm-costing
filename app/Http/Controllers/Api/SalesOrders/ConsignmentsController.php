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
        $this->validate($request, [
            'invoice_amt'       => 'required|numeric|min:0|max:99999999',
            'reference'         => 'required|min:0|max:30',
            'supplier_ref'      => 'sometimes|min:0|max:30',
            'our_order_number'  => 'sometimes|min:0|max:30',
            'vessel_name'       => 'sometimes|min:0|max:30',
            'container_number'  => 'sometimes|min:0|max:30',
            'bl_number'         => 'sometimes|min:0|max:30',
            'shipping_agent_ref' => 'sometimes|min:0|max:30',
            'current_fx_rate'   => 'nullable|sometimes|numeric|min:0|max:999'
        ]);

        $consignment = new Consignment([
            'reference'                 => $request->input('reference', ''),
            'supplier_ref'              => $request->input('supplier_ref', ''),
            'our_order_number'          => $request->input('our_order_number', ''),
            'vessel_name'               => $request->input('vessel_name', ''),
            'container_number'          => $request->input('container_number', ''),
            'bl_number'                 => $request->input('bl_number', ''),
            'shipping_agent_ref'        => $request->input('shipping_agent_ref', ''),
            'invoice_amt'               => $request->input('invoice_amt', ''),
            'current_fx_rate'           => $request->input('current_fx_rate')
        ]);
        $consignment->sales_order()->associate($salesOrder);
        $consignment->creator()->associate(\Auth::user());
        $consignment->save();
    }
}
