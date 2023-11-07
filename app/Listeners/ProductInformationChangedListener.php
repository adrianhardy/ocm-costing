<?php

namespace App\Listeners;

use App\Events\ProductInformationChanged;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProductInformationChangedListener
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
    public function handle(ProductInformationChanged $event)
    {
        $lines = SalesOrderLine::query()
            ->where('product_id', $event->product->id)
            ->whereHas('sales_order', function($query) {
                return $query->currentStatus(SalesOrder::STATUS_DRAFT);
            })
            ->get();

        /** @var SalesOrderLine $line */
        foreach ($lines as $line) {
            $line->fill($event->product->only([
                'item_number',
                'article_number',
                'packing_instructions',
                'product_description',
                'name',
                'barcode',
                'units_per_case',
                'inner_qty',
                'case_weight_kg',
                'height_m',
                'length_m',
                'width_m',
                'unit_weight_kg',
                'unit_height_m',
                'unit_length_m',
                'unit_width_m',
                'unit_price',
                'est_landed_cost_gbp'
            ]));

            $line->total = $line->unit_price * $line->units_requested;
            $line->total_gbp = $line->total * $line->sales_order->fx_rate;
            // 25/10/23 AH - Don't trash the unit_price_gbp which would've come from
            // an invoice with our months out of date USD price....
            // $line->unit_price_gbp = $line->unit_price * $line->sales_order->fx_rate;

            $line->save();
            $line->sales_order->touch();

        }

    }
}
