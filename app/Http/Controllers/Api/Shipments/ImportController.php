<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Mail\NewInboundShipmentReadyNotification;
use App\Models\Consignment;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Transformers\SalesOrderLineTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ImportController extends Controller
{
    /**
     * This controller is a bit of a mess. We're doing both the preview and
     * the real import in one place. The invoice import is very simple to
     * do and understand, but the packing list import is difficult ...
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        set_time_limit(6000);

        $file = $request->file('file');
        $type = $request->input('type');
        $preview = $request->input('preview') === '1';

        $this->validate($request, [
            'file' => 'file|required|max:20000|mimes:xlsx,xls',
            'type' => 'required|in:invoice,packing'
        ]);

        if ($type == 'packing' && !$preview) {
            $this->validate($request, [
                'container_reference'           => 'required|max:50',
                'vessel_name'                   => 'required|min:1|max:50',
                'bl_number'                     => 'nullable|min:0|max:32',
                'shipping_agent_ref'            => 'nullable|min:0|max:200',
                'reference'                     => 'required|min:0|max:200',
                'supplier_reference'            => 'required|min:0|max:200',
                'total_shipping_invoice_value'  => 'required|numeric|min:0',
                'current_fx_rate'               => 'required|numeric|min:0'
            ]);
        }

        // convert the XLSX file to JSON ...
        Artisan::call("import:shipping-file --type=$type {$file->getRealPath()} auto");
        $json = json_decode(Artisan::output(), true);
        $summary = collect();
        $uniqueSalesOrderReferences = [];
        $consignment = null;
        DB::beginTransaction();
        $log = [];
        // For every line found in the XLSX file
        foreach ($json['lines'] as $row) {

            /** @var SalesOrderLine $line */
            $line = SalesOrderLine
                ::query()
                ->where('article_number', $row['article_number']);

            if ($row['po_number']) {
                $line->whereHas('sales_order', function ($query) use ($row) {
                    $query->where('reference', $row['po_number']);
                });
            }

            /** @var SalesOrderLine $line */
            $line = $line->first();

            if (!$line) {
                $log[] = "Failed to find article {$row['article_number']} on PO {$row['po_number']}";
                continue;
            }

            if (!in_array($line->sales_order->reference, $uniqueSalesOrderReferences)) {
                $uniqueSalesOrderReferences[] = $line->sales_order->reference;
            }

            if ($type == 'invoice' && !$preview) {
                $line->units_requested = $row['qty'];
                $line->unit_price = $row['unit_price'];
                $line->unit_price_gbp = $line->unit_price * $line->sales_order->fx_rate;
                $line->save();
                $line->sales_order->touch();
            }

            // If we're trying to update shipments as we're going lone
            if ($type == 'packing') {

                if (!isset($json['shipment']['container_reference'])) {
                    abort(400, 'Failed to identify a container reference in the packing list');
                }

                $consignment = Consignment::query()
                    ->where('container_number', $json['shipment']['container_reference'])
                    ->first();
                if (!$consignment) {
                    $consignment = new Consignment([
                        'container_number' => $json['shipment']['container_reference'],
                    ]);
                }
                $consignment->fill([
                    'reference' => $request->input('reference'),
                    'supplier_ref' => $request->input('supplier_reference'),
                    'vessel_name' => $json['shipment']['vessel_name'],
                    'estimated_arrival_at' => Carbon::parse($json['shipment']['estimated_date_of_arrival']),
                    'estimated_departure_at' => Carbon::parse($json['shipment']['estimated_date_of_departure']),
                    'our_order_number' => implode(',', $uniqueSalesOrderReferences),
                    'invoice_amt' => $request->input('total_shipping_invoice_value'),
                    'bl_number' => $request->input('bl_number'),
                    'current_fx_rate' => $request->input('current_fx_rate')
                ]);

                // TODO: set the width/length/height_cm
                $line->width_m = $row['outer_dimension_w'];
                $line->length_m = $row['outer_dimension_l'];
                $line->height_m = $row['outer_dimension_h'];
                $line->units_per_case = $row['pcs_per_ctn'];

                $log[] = [
                    'desc' => "Saved a shipment line for article {$row['article_number']}",
                    'line' => $line->only(['unit_width_cm', 'unit_length_cm', 'unit_height_cm', 'units_per_case'])
                ];

                if (!$preview) {
                    $line->save();
                    $consignment->sales_order()->associate($line->sales_order);
                    $consignment->save();
                    $consignment->touch();

                    // attach this line to this consignment
                    // if it doesn't exist already ...
                    if (!$consignment
                        ->sales_order_lines()
                        ->where('id', $line->id)
                        ->exists()
                    ) {
                        $consignment->sales_order_lines()->save($line);
                    }

                    $consignment->sales_order_lines()
                        ->wherePivot('consignment_id', $consignment->id)
                        ->wherePivot('sales_order_line_id', $line->id)
                        ->update([
                            'qty_shipped' => $row['qty']
                        ]);
                }
            }

            $summary[] = [
                'line' => fractal($line, new SalesOrderLineTransformer())->parseIncludes('product'),
                'raw' => $row
            ];


            if ($consignment && $consignment->wasChanged(['estimated_arrival_at']) && !$preview) {
                $mail = new NewInboundShipmentReadyNotification($consignment);
                $mail
                    ->to('shipping-alerts@orchardcrownmall.co.uk', 'Warehouse')
                    ->subject('Inbound shipment update');

                Mail::send($mail);
            }
        }

        DB::commit();
        return response()->json([
            'lines' => $summary,
            'shipment' => $json['shipment'],
            'log' => $log
        ]);
    }
}
