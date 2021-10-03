<?php

namespace App\Http\Controllers\Supplier\SalesOrders;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SalesOrdersController extends Controller
{
    public function edit(SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);
        return response()->view('supplier.sales-orders.edit', [
            'so' => $salesOrder
        ]);
    }
}
