<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Domain\LandedCostCalculator;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\ProductLandedCost;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\PricingService;

class CalculateLandedCostController extends Controller
{
    /**
     * Run the calcaultions based on what's been received and
     * how much space it's taken up on the consignments.
     *
     * Once we've done that, we'll also calculate a landed cost
     * and document it against the product.
     *
     *
     * @param Request $request
     * @param SalesOrder $salesOrder
     */
    public function store(
        Request $request,
        Consignment $shipment,
        PricingService $pricingService
    ) {
        $cubicFt = 0;
        foreach ($shipment->sales_order_lines as $line) {
            $cubicFt += ($line->pivot->qty_shipped / $line->units_per_case) * $line->case_cubic_ft;
        }

        $shipment->est_cost_cuft_gbp = $shipment->invoice_amt / $cubicFt;
        $shipment->save();

        foreach ($shipment->sales_order_lines as $line) {
            $calculator = new LandedCostCalculator(
                $shipment->est_cost_cuft_gbp
            );
            $calcs = $calculator->calculateWithDuties(
                ceil($line->pivot->qty_shipped / $line->units_per_case) * $line->case_cubic_ft,
                $line->pivot->qty_shipped,
                $line->unit_price,
                $line->product->is_excluded_from_commission
            );

            $shipment->sales_order_lines()
                ->wherePivot('consignment_id', $shipment->id)
                ->wherePivot('sales_order_line_id', $line->id)
                ->update([
                    'shipping_cost_gbp_per_unit' => $calcs['shipping_cost_per_unit_gbp'],
                    'est_landed_cost_per_unit_gbp' => $calcs['est_landed_cost_per_unit_gbp']
                ]);

            $landedCost = new ProductLandedCost([
                'units_per_case' => $line->units_per_case,
                'date' => Carbon::now(),
                'length_m' => $line->length_m,
                'height_m' => $line->height_m,
                'width_m' => $line->width_m,
                'fx_rate' => $line->sales_order->fx_rate,
                'unit_price' => $line->unit_price,
                'import_duty_pc' => setting('default_import_duty_pc'),
                'commission_rate_pc' => setting('default_commission_rate_pc'),
                'shipping_cost_per_cuft' => $shipment->est_cost_cuft_gbp,
                'landed_cost_per_unit' => 0,
                'landed_cost_per_unit_gbp' => $calcs['est_landed_cost_per_unit_gbp']
            ]);
            $landedCost->currency()->associate($line->sales_order->currency);
            $landedCost->product()->associate($line->product);
            $landedCost->save();

            if ($request->input('send_to_pricing')) {
                $pricingService->sendToPricing($line);
            }
        }

        return response()->json();
    }
}
