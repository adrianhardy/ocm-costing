<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Domain\LandedCostCalculator;
use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Consignment;
use App\Models\Repositories\SalesOrderLineRepository;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\ConsignmentTransformer;
use App\Transformers\SalesOrderLineTransformer;
use Illuminate\Http\Request;

class ConsignmentsController extends Controller
{
    public function index(Request $request,
                          SalesOrder $salesOrder,
                          SalesOrderLineRepository $salesOrderLineRepository
    ) {
        $paginator = $salesOrderLineRepository->whereHas('sales_order', function($query) use ($salesOrder) {
            return $query->where('sales_order_id', $salesOrder->id);
        });

        $paginator = $paginator->where('sales_order_id', $salesOrder->id);

        $paginator = $paginator->paginate(
            $request->input('per_page', $paginator->count())
        );

        return response()->json(new SerializeLengthAwarePaginator($paginator, new SalesOrderLineTransformer()));

    }

    /**
     * @param Request $request
     * @param SalesOrderLine $line
     * @param Consignment $consignment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        SalesOrderLine $line,
        Consignment $consignment
    ) {
        $line->consignments()
            ->wherePivot('consignment_id', $consignment->id)
            ->update([
                'qty_shipped' => $request->input('pivot.qty_shipped')
            ]);
        $line->consignments()
            ->wherePivot('consignment_id', $consignment->id)
            ->update(['invoice_amt' => $request->input('pivot.invoice_amt')
            ]);

        $shipped = $line->consignments()
            ->wherePivot('consignment_id', $consignment->id)
            ->first()
            ->pivot
            ->qty_shipped;

        $invoiceAmount = $line->consignments()
            ->wherePivot('consignment_id', $consignment->id)
            ->first()
            ->pivot
            ->invoice_amt;

        $calculator = new LandedCostCalculator();
        $calculate = $calculator->calculateFromSalesOrderLine(
            $line,
            $shipped,
            $invoiceAmount
        );

        $line->consignments()
            ->wherePivot('consignment_id', $consignment->id)
            ->update([
                'goods_cost'                   => $calculate['goods_cost'],
                'goods_cost_gbp'               => $calculate['goods_cost_gbp'],
                'shipping_cost_gbp'            => $calculate['shipping_cost_gbp'],
                'shipping_cost_gbp_per_unit'   => $calculate['shipping_cost_gbp_per_unit'],
                'est_landed_cost_per_unit'     => $calculate['est_landed_cost_per_unit'],
                'est_landed_cost_per_unit_gbp' => $calculate['est_landed_cost_per_unit_gbp']
            ]);
        $line->save();

        return response()->json
            (fractal($consignment->fresh(), new ConsignmentTransformer())
        );

    }
}
