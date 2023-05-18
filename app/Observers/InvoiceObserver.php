<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\SalesOrder;

class InvoiceObserver
{
    public function saving(Invoice $invoice){
        $invoice->sub_total = $invoice->invoice_lines()->sum('sub_total');
    }

    /**
     * If the invoice doesn't require approval, or is approved AND
     *  - look at the sales order, check every line on that sales order
     *  - make sure all invoices for that line have been approved
     *  - confirm that the TOTAL number invoiced matches the OA line's qty
     *    requested
     *
     * @param Invoice $invoice
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function saved(Invoice $invoice){

        $isAutoClosable = null;

        if(!$invoice->is_approval_required  || $invoice->approved_at) {
            foreach ($invoice->sales_order->lines as $line) {

                $totalInvoiced = InvoiceLine::query()
                    ->where('sales_order_line_id', $line->id)
                    ->whereHas('invoice', function ($query) {
                        return $query->whereNotNull('approved_at')
                            ->orWhere('is_approval_required', false);
                    })
                    ->sum('qty');


                if ($line->units_requested == $totalInvoiced) {
                    $isAutoClosable = true;
                } else {
                    $isAutoClosable = false;
                    break;
                }
            }

            if ($isAutoClosable === true) {
                $invoice->sales_order->setStatus(SalesOrder::OA_STATUS_CLOSED)->save();
            }
        }
    }


}
