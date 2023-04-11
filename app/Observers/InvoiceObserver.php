<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function saving(Invoice $invoice){
        $invoice->sub_total = $invoice->invoice_lines()->sum('sub_total');
    }
}
