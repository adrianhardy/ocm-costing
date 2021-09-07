<?php

namespace App\Observers;

use App\Models\SalesOrder;

class SalesOrderObserver
{
    public function saving(SalesOrder $salesOrder)
    {
        $salesOrder->goods_cost = $salesOrder->lines()->sum('total');
        $salesOrder->goods_cost_gbp = $salesOrder->lines()->sum('total_gbp');
        $salesOrder->shipping_cost = $salesOrder->lines()->sum('total_cubic_m')
            * setting('cubic_metres_to_feet')
            * setting('shipping_cost_per_cubic_foot_usd');
        $salesOrder->shipping_cost_gbp = $salesOrder->shipping_cost * $salesOrder->fx_rate;

        $salesOrder->total = $salesOrder->goods_cost + $salesOrder->shipping_cost;
        $salesOrder->total_gbp = $salesOrder->goods_cost_gbp + $salesOrder->shipping_cost_gbp;
    }
}
