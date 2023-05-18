<?php

namespace App\Http\Controllers\Api\Uk\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Repositories\InvoiceRepository;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Services\InvoiceService;
use App\Services\PricingService;
use App\Transformers\InvoiceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceRepository $repository)
    {
        $paginator = $repository->pushCriteria(app(RequestCriteria::class));


        if($request->input('product_id')) {
            $paginator = $paginator->whereHas('invoice_lines', function($query) use ($request) {
                return $query->whereHas('sales_order_line', function($query) use ($request) {
                    return $query->where('product_id', $request->input('product_id'));
                });
            });
        }

        switch ($request->input('stage')) {
            case 'approved':
                $paginator = $paginator->where('is_approval_required', false)->orWhereNotNull('approved_at');
                break;
            case 'approval_needed':
                $paginator = $paginator->where('is_approval_required', true)->whereNull('approved_at');
                break;
            case 'deleted':
                $paginator = $paginator->withTrashed()->whereNotNull('deleted_at');
                break;
        }

        $paginator = $paginator->paginate($request->input('per_page', 15));


        return response()->json(new SerializeLengthAwarePaginator($paginator, new InvoiceTransformer()));
    }

    public function show(Request $request, Invoice $invoice)
    {
        return response()->json(fractal($invoice, new InvoiceTransformer));
    }


    public function destroy(Request $request, Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(fractal($invoice, new InvoiceTransformer));
    }

    /**
     * @param Request $request
     * @param PricingService $pricingService
     * @param InvoiceService $invoiceService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PricingService $pricingService, InvoiceService $invoiceService)
    {

        // work out how many lines they've checked before we go any further
        $checked = array_filter($request->input('lines'), function($line){
            return isset($line['checked']);
        });

        abort_unless(
            count($checked) > 0,
            400,
            'You must check at least one OA line to create an invoice'
        );


        // we're basically just giving this a sales order,
        // so we need to go through the lines and pick out
        // the ones that have been selected, and create invoice lines for them
        $this->validate($request, [
            'invoice_reference' => 'required|unique:invoices,reference',
            'invoice_date' => 'required|date',
            'id' => 'required',
            'lines' => 'required|array|min:1',
            'lines.*.product.id'    => 'required',
            'lines.*.checked'       => 'sometimes|boolean',
            'lines.*.invoice_qty_received'  => 'nullable|numeric|min:0|required_if:lines.*.checked,true',
            'lines.*.invoice_unit_price'    => 'nullable|numeric|min:0|required_if:lines.*.checked,true',
            'lines.*.priority' => 'required_if:lines.*.checked,true|sometimes|nullable|in:low,medium,high',
        ], [
            'lines.*.invoice_qty_received.required_if' => 'Qty received is required when line is checked',
            'lines.*.invoice_unit_price.required_if' => 'Unit price is required when line is checked',
            'lines.*.priority.required_if' => 'Pricing Priority is required when line is checked'
        ]);

        // @todo make sure that the validation here works properly and looks nice too

        /** @var SalesOrder $salesOrder */
        $salesOrder = SalesOrder::query()->findOrFail($request->input('id'));

        DB::beginTransaction();

        $invoice = new Invoice();

        $invoice->reference = $request->input('invoice_reference');
        $invoice->invoice_date = $request->input('invoice_date');
        $invoice->creator()->associate(auth()->user());
        $invoice->sales_order()->associate($salesOrder);

        $invoice->save();


        foreach ($request->input('lines') as $line) {
            if (isset($line['checked']) && $line['checked'] === true) {
                /** @var SalesOrderLine $salesOrderLine */
                $salesOrderLine = SalesOrderLine::query()->findOrFail($line['id']);

                $invoiceLine = new InvoiceLine();
                $invoiceLine->sales_order_line()->associate($salesOrderLine);
                $invoiceLine->invoice()->associate($invoice);
                $invoiceLine->unit_price = $line['invoice_unit_price'];
                $invoiceLine->qty = $line['invoice_qty_received'];
                $invoiceLine->pricing_priority = $line['priority'];
                $invoiceLine->save();
            }
        }

        if($invoiceService->checkIfInvoiceApprovalRequired($invoice)) {
            $invoice->is_approval_required = true;
        } else {
            foreach ($invoice->invoice_lines as $line) {
                $pricingService->sendToPricing($line->sales_order_line->product, $line->unit_price, $line->pricing_priority);
            }
        }


        // we have an observer to set sub_total for the invoice once all the lines are processed
        $invoice->save();
        $invoice->sales_order->touch();

        DB::commit();

        return response()->json(fractal($invoice, new InvoiceTransformer));
    }

    /**
     * @param Request $request
     * @param PricingService $pricingService
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function update(Request $request, PricingService $pricingService, Invoice $invoice)
    {

        $this->validate($request, [
            'lines.*.unit_price' => 'numeric|min:0|required',
            'lines.*.qty' => 'numeric|min:0|required',
            'lines.*.pricing_priority' => 'required|in:low,medium,high',
        ]);


        foreach ($request->input('lines') as $line) {
            /** @var InvoiceLine $invoiceLine */
            $invoiceLine = InvoiceLine::query()->findOrFail($line['id']);
            $invoiceLine->unit_price = $line['unit_price'];
            $invoiceLine->qty = $line['qty'];
            $invoiceLine->pricing_priority = $line['pricing_priority'];
            $invoiceLine->save();

            $pricingService->sendToPricing($invoiceLine->sales_order_line->product, $invoiceLine->unit_price, $invoiceLine->pricing_priority);
        }

        $invoice->approved_at = Carbon::now();
        $invoice->approver()->associate(auth()->user());

        if ($request->input('close_oa')) {
            $invoice->sales_order->setStatus(SalesOrder::OA_STATUS_CLOSED);
        }


        $invoice->save();
        $invoice->sales_order->save();


        return response()->json(fractal($invoice, new InvoiceTransformer()));

    }


}
