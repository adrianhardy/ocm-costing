<?php

namespace App\Http\Controllers\SalesOrders;

use App\Domain\Excel\SalesOrderExporter;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class ExportController extends Controller
{

    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     */
    public function index(Request $request, SalesOrder $salesOrder)
    {
        $template = new \SplFileInfo(storage_path('app/private/sales_order_template.xlsx'));
        $exporter = new SalesOrderExporter($template);
        $resource = $exporter->generate($salesOrder);

        return response()->streamDownload(function() use ($resource) {
            fpassthru($resource);
        }, "{$salesOrder->sales_order_number}.xlsx");

    }
}
