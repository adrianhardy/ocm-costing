<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Consignment;
use App\Models\Repositories\ConsignmentRepository;
use App\Transformers\ConsignmentTransformer;
use Illuminate\Http\Request;

class ShipmentsController extends Controller
{
    public function index(Request $request, ConsignmentRepository $repository)
    {
        $paginator = $repository;

        $paginator = $paginator->paginate($request->input('per_page', 100));

        return response()->json(new SerializeLengthAwarePaginator($paginator, new ConsignmentTransformer()));
    }

    public function show(Consignment $shipment)
    {
        return response()->json(fractal($shipment, new ConsignmentTransformer()));
    }

    public function update(Request $request, Consignment $shipment)
    {
        $this->validate($request, [
            'reference' => 'required',
            'sales_order' => 'required',
            'est_cost_cuft_gbp' => 'required',
            'our_order_number' => 'required',
            'vessel_name' => 'required',
            'supplier_ref' => 'required',
            'container_number' => 'required',
            'bl_number' => 'required',
            'shipping_agent_ref' => 'required'
        ]);

        $shipment->fill($request->only([
            'reference',
            'est_cost_cuft_gbp',
            'our_order_number',
            'vessel_name',
            'supplier_ref',
            'container_number',
            'bl_number',
            'shipping_agent_ref',
            'departed_at',
            'docked_at'
        ]));

        foreach ($shipment->sales_order_lines as $line) {
            $consignment = $line->consignments[0];
        }

        $shipment->save();
    }
}
