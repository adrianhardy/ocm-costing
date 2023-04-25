<?php

namespace App\Observers;

use App\Models\InvoiceLine;

class InvoiceLineObserver
{
    public function saving(InvoiceLine $invoiceLine){
        $invoiceLine->sub_total = $invoiceLine->unit_price * $invoiceLine->qty;
    }

    public function saved(InvoiceLine $invoiceLine){
        $invoiceLine->invoice->touch();
    }
}
