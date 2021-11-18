<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Domain\LandedCostCalculator;
use App\Http\Controllers\Controller;
use App\Models\Base\SalesOrder;
use App\Models\Consignment;
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
            $invoiceAmount = $data['invoice_amt'];


            $calculator = new LandedCostCalculator();
            $new = $calculator->calculateFromSalesOrderLine(
                $soLine,
                $data['qty_on_boat'],
                $invoiceAmount
            );

            // snapshot the landed cost
            $product = $soLine->product;

            $landedCost = new ProductLandedCost([
                'units_per_case' => $product->units_per_case,
                'date' => Carbon::now(),
                'length_m' => $product->length_m,
                'height_m' => $product->height_m,
                'width_m' => $product->width_m,
                'fx_rate' => $soLine->sales_order->fx_rate,
                'shipping_cost_per_cuft_usd' => setting('shipping_cost_per_cubic_foot_usd'),
                'import_duty_pc' => setting('default_import_duty_pc'),
                'commission_rate_pc' => setting('default_commission_rate_pc'),
                'landed_cost_per_unit' => $new['est_landed_cost_per_unit'],
                'landed_cost_per_unit_gbp' => $new['est_landed_cost_per_unit_gbp']
            ]);
            $landedCost->currency()->associate($soLine->sales_order->currency);
            $landedCost->product()->associate($product);
            $landedCost->save();

            $shipment->sales_order_lines()->save($soLine, $new);
            $shipment->save();

            $inspection = InspectionReport::createFromSalesOrderLine($soLine);
            $inspection->consignment()->associate($shipment);
            $inspection->save();
            $inspection->setStatus(InspectionReport::STATUS_PENDING, 'pending');
            $inspection->save();

        }
        DB::commit();

        return response()->json($shipment);
    }
}
