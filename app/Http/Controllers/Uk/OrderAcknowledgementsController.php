<?php

namespace App\Http\Controllers\Uk;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class OrderAcknowledgementsController extends Controller
{
    public function index(Request $request){
        return view('uk.order-acknowledgements.index');
    }

    public function edit(Request $request, SalesOrder $orderAcknowledgement){
        return view('uk.order-acknowledgements.edit', compact('orderAcknowledgement'));
    }

}
