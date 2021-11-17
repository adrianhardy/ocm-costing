<?php

namespace App\Http\Controllers\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesOrdersController extends Controller
{
    public function index()
    {
        return response()->view('sales-orders.index');
    }

    public function edit(SalesOrder $sales_order)
    {
        $templates = Storage::disk('export_templates')->allFiles('sales-orders');
        return response()->view('sales-orders.edit', [
            'so' => $sales_order,
            'templates' => $templates
        ]);
    }
}
