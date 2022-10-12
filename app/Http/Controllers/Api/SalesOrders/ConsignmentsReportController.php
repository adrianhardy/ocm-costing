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

class ConsignmentsReportController extends Controller
{

    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     * @param SalesOrderLineRepository $salesOrderLineRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,
                          SalesOrder $salesOrder,
                          SalesOrderLineRepository $salesOrderLineRepository
    ) {
        $paginator = $salesOrderLineRepository->whereHas('sales_order', function($query) use ($salesOrder) {
            return $query->where('sales_order_id', $salesOrder->id);
        });

        $paginator = $paginator->where('sales_order_id', $salesOrder->id);

        if ($request->input('all')) {
            return response()->json([
                'items' => fractal($paginator->get(), new SalesOrderLineTransformer())
            ]);
        }

        $paginator = $paginator->paginate($request->input('per_page'));
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
        return response()->json
            (fractal($consignment->fresh(), new ConsignmentTransformer())
        );

    }
}
