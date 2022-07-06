<?php

namespace App\Http\Controllers\Shipments;

use App\Http\Controllers\Controller;
use App\Models\Consignment;
use Illuminate\Http\Request;

class ShipmentsController extends Controller
{
    public function index(){
        return view('shipments.index');
    }

    public function show(Consignment $shipment){
        return view('shipments.edit', [
            'shipment' => $shipment
        ]);
    }
}
