<?php

namespace App\Http\Controllers\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrdersController extends Controller
{
    public function index()
    {
        return response()->view('sales-orders.index');
    }

    public function edit(SalesOrder $sales_order)
    {
        return response()->view('sales-orders.edit', [
            'so' => $sales_order
        ]);
    }
}
