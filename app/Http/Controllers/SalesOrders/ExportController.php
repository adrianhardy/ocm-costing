<?php

namespace App\Http\Controllers\SalesOrders;

use App\Domain\Excel\SalesOrderExporter;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{

    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     */
    public function index(Request $request, SalesOrder $salesOrder)
    {
        $template = new \SplFileInfo(
            Storage::disk('export_templates')->path($request->query('template'))
        );
        abort_unless($template->isFile() && $template->isReadable(), 400, 'Template not found');
        $exporter = new SalesOrderExporter($template);
        $blob = $exporter->generate($salesOrder);

        return response()->streamDownload(function() use ($blob) {
            echo $blob;
        }, "{$salesOrder->reference}.xls");

    }
}
