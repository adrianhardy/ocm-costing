<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Domain\LandedCostCalculator;
use App\Http\Controllers\Controller;
use App\Models\Base\SalesOrder;
use App\Models\Consignment;
use App\Models\Currency;
use App\Models\InspectionReport;
use App\Models\ProductLandedCost;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
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
            'lines.*.id'                    => 'required|exists:sales_order_lines,id',
            'lines.*.qty_on_boat'           => 'required|numeric|min:1|max:1000000',
            'shipment.id'                   => 'required|exists:consignments,id'
        ], [], [
            'shipment.id'                   => 'selected shipment',
            'fx_rate'                       => 'exchange rate'
        ]);


        DB::beginTransaction();
        $shipment = Consignment::query()->findOrFail($request->input('shipment.id'));

        foreach ($request->input('lines.*') as $data) {

            /** @var SalesOrderLine $soLine */
            $soLine = SalesOrderLine::query()->findOrFail($data['id']);

            if (DB::table('sales_order_lines_consignments')
                ->where('sales_order_line_id', $soLine->id)
                ->where('consignment_id', $shipment->id)
                ->exists()
            ) {
                $soLine->consignments()
                    ->wherePivot('consignment_id', $shipment->id)
                    ->update([
                        'qty_shipped' => $data['qty_on_boat']
                    ]);
            } else {
                $shipment->sales_order_lines()->save($soLine, [
                    'qty_shipped' => $data['qty_on_boat']
                ]);
            }

            $inspection = InspectionReport::createFromSalesOrderLine($soLine);
            $inspection->consignment()->associate($shipment);
            $inspection->save();
            $inspection->setStatus(InspectionReport::STATUS_PENDING, 'pending');
            $inspection->save();

            /*
            if (isset($data['done']) && $data['done']) {
                $landedCost = new ProductLandedCost([
                    'units_per_case' => $soLine->product->units_per_case,
                    'date' => Carbon::now(),
                    'length_m' => $soLine->product->length_m,
                    'height_m' => $soLine->product->height_m,
                    'width_m' => $soLine->product->width_m,
                    'fx_rate' => $soLine->sales_order->fx_rate,
                    'unit_price' => $soLine->product->unit_price,
                    'import_duty_pc' => setting('default_import_duty_pc'),
                    'commission_rate_pc' => setting('default_commission_rate_pc'),
                    'shipping_cost_per_cuft' => 0,
                    'landed_cost_per_unit' => 0,
                    'landed_cost_per_unit_gbp' => 0
                ]);
                $landedCost->currency()->associate($soLine->sales_order->currency);
                $landedCost->product()->associate($soLine->product);
                $landedCost->save();

            }
            */

        }
        DB::commit();

        return response()->json($shipment);
    }
}
