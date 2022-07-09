<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class RestoreSalesOrderController extends Controller
{
    public function restore(Request $request){
        $salesOrder = SalesOrder::query()->withTrashed()->where('id', $request->input('id'))->first();
        $salesOrder->restore();

    }
}
