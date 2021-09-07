<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Base\SalesOrder;
use App\Models\Consignment;
use App\Models\SalesOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateShipmentFromOrderLinesController extends Controller
{
    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'reference'             => 'required|min:1|max:32',
            'lines.*.id'            => 'required|exists:sales_order_lines,id',
            'lines.*.qty_on_boat'   => 'required|numeric|min:1|max:1000000',
            'lines.*.invoice_amt'   => 'required|numeric|min:1|max:1000000'
        ]);

        DB::beginTransaction();
        $shipment = new Consignment(
            $request->only(['reference'])
        );
        $shipment->save();

        foreach ($request->input('lines.*') as $data) {

            /** @var SalesOrderLine $soLine */
            $soLine = SalesOrderLine::query()->findOrFail($data['id']);
            $fx = $soLine->sales_order->fx_rate;
            $invoiceAmount = $data['invoice_amt'];
            $goodsTotal = $soLine->unit_price * $data['qty_on_boat'];
            $shippingCost = $invoiceAmount - $goodsTotal;
            $shippingCostPerUnit = $shippingCost / $data['qty_on_boat'];

            $shipment->sales_order_lines()->save($soLine, [
                'qty_shipped' => $data['qty_on_boat'],
                'goods_cost' => $goodsTotal,
                'goods_cost_gbp' => $goodsTotal * $fx,
                'shipping_cost_gbp' =>  $shippingCost * $fx,
                'shipping_cost_gbp_per_unit' => $shippingCostPerUnit * $fx,
                'est_landed_cost_per_unit' => $soLine->unit_price + $shippingCostPerUnit,
                'est_landed_cost_per_unit_gbp' => $soLine->unit_price_gbp + ($shippingCostPerUnit * $fx),
                'invoice_amt' => $invoiceAmount
            ]);
        }
        DB::commit();

        return response()->json($shipment);
    }
}
