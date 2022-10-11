<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Mail\NewInboundShipmentReadyNotification;
use App\Models\Consignment;
use App\Models\Repositories\ConsignmentRepository;
use App\Transformers\ConsignmentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
            'reference' => 'required|min:0|max:200',
            'sales_order' => 'required',
            'est_cost_cuft_gbp' => 'sometimes|nullable',
            'our_order_number' => 'required|min:0|max:200',
            'vessel_name' => 'required|min:0|max:32',
            'supplier_ref' => 'sometimes|min:0|max:200',
            'container_number' => 'required|min:0|max:32',
            'bl_number' => 'sometimes|min:0|max:32',
            'shipping_agent_ref' => 'sometimes|min:0|max:200',
            'invoice_amt' => 'sometimes|numeric|min:0'
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
            'invoice_amt'
        ]));

        if ($request->input('departed_at')) {
            $shipment->departed_at = Carbon::parse($request->input('departed_at'));
        } else {
            $shipment->departed_at = null;
        }

        if ($request->input('docked_at')) {
            $shipment->docked_at = Carbon::parse($request->input('docked_at'));
        } else {
            $shipment->docked_at = null;
        }

        if ($request->input('estimated_arrival_at')) {
            $shipment->estimated_arrival_at = Carbon::parse($request->input('estimated_arrival_at'));
        } else {
            $shipment->estimated_arrival_at = null;
        }

        if ($request->input('estimated_departure_at')) {
            $shipment->estimated_departure_at = Carbon::parse($request->input('estimated_departure_at'));
        } else {
            $shipment->estimated_departure_at = null;
        }

        if ($request->input('booked_in_at')) {
            $shipment->booked_in_at = Carbon::parse($request->input('booked_in_at'));
        } else {
            $shipment->booked_in_at = null;
        }
        $shipment->save();

        if ($shipment->wasChanged(['estimated_arrival_at'])) {
            $mail = new NewInboundShipmentReadyNotification($shipment);
            $mail
                ->to('shipping-alerts@orchardcrownmall.co.uk', 'Warehouse')
                ->subject('Inbound shipment update');

            Mail::send($mail);
        }

    }

    /**
     * @param Consignment $shipment
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Consignment $shipment)
    {
        abort_unless(Auth::user()->hasRole(['super']), 403, 'You are not permitted to carry out this action');
        $shipment->delete();
        return response()->json();
    }
}
