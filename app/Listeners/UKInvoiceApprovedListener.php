<?php

namespace App\Listeners;

use App\Models\InvoiceLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UKInvoiceApprovedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        /** @var InvoiceLine $line */
        foreach($event->invoice->invoice_lines as $line) {
            $product = $line->sales_order_line->product;
            $product->unit_price_gbp = $line->unit_price;
            $product->save();
        }
    }
}
