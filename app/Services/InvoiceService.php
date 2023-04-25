<?php


namespace App\Services;


use App\Models\Invoice;

class InvoiceService
{
    public function checkIfInvoiceApprovalRequired(Invoice $invoice)
    {
        $approvalRequired = false;

        foreach($invoice->invoice_lines as $line) {

            // evaluating qty_remaing here will mean that this line
            // is included in the SUM, we need to remove it here so
            // we can properly check if this is the line that brought it over
            $remaining = $line->sales_order_line->qty_remaining + $line->qty;

            if($line->unit_price !== $line->sales_order_line->unit_price || $line->qty > $remaining) {
                $approvalRequired = true;
            }
        }

        return $approvalRequired;
    }

}