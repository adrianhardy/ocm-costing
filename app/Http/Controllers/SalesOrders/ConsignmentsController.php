<?php

namespace App\Http\Controllers\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class ConsignmentsController extends Controller
{
    public function index(SalesOrder $salesOrder)
    {
        return view('sales-orders.consignments.index', [
            'salesOrder' => $salesOrder
        ]);
    }
}
