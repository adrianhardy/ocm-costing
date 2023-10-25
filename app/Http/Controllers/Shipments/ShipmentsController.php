<?php

namespace App\Http\Controllers\Shipments;

use App\Domain\Excel\InspectionSheetBuilder;
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

    /**
     * @param Consignment $consignment
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request, Consignment $consignment)
    {
        $this->validate($request, [
            'mode' => 'required|in:shipping,inspection'
        ]);

        $filename = "INSPECTION REPORT {$consignment->sales_order->reference}.xlsx";

        if ($request->input('mode') == 'shipping') {
            $filename = "LANDED COST REPORT -- {$consignment->sales_order->reference}.xlsx";
        }

        $builder = new InspectionSheetBuilder();
        $fp = $builder->build($consignment, $request->input('mode'));
        return response()->streamDownload(function () use ($fp) {
            rewind($fp);
            echo stream_get_contents($fp);
        }, $filename);
    }
}
