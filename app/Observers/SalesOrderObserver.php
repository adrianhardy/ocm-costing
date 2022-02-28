<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Models\Version;
use App\Transformers\SalesOrderTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesOrderObserver
{
    public function creating(SalesOrder $salesOrder)
    {
        $salesOrder->fx_rate = $salesOrder->currency->default_fx_to_gbp;
    }

    /**
     * Re-total the shipment based on line totals and current
     * shipping_cost_per_cubic_foot
     *
     * @param SalesOrder $salesOrder
     * @return void
     */
    public function saving(SalesOrder $salesOrder)
    {
        $salesOrder->goods_cost = $salesOrder->lines()->sum('total');
        $salesOrder->goods_cost_gbp = $salesOrder->lines()->sum('total_gbp');
        $salesOrder->shipping_cost_gbp = $salesOrder->lines()->sum('total_cubic_m')
            * setting('cubic_metres_to_feet')
            * $salesOrder->shipping_cost_per_cubic_foot;

        $salesOrder->shipping_cost = $salesOrder->shipping_cost_gbp / $salesOrder->fx_rate;
        $salesOrder->total = $salesOrder->goods_cost + $salesOrder->shipping_cost;
        $salesOrder->total_gbp = $salesOrder->goods_cost_gbp + $salesOrder->shipping_cost_gbp;

        if (!$salesOrder->shipping_cost_per_cubic_foot) {
            $salesOrder->shipping_cost_per_cubic_foot = setting('shipping_cost_per_cubic_foot');
        }
    }

    /**
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(SalesOrder $salesOrder)
    {
        $json = fractal($salesOrder)
            ->parseIncludes(['lines', 'lines.product'])
            ->transformWith(new SalesOrderTransformer())
            ->toArray();

        $version = new Version();
        $version->model_data = $json;
        $version->user()->associate(\Auth::user());
        $salesOrder->history()->save($version);

        return response()->json([], 201);
    }
}
