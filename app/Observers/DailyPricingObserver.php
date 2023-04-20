<?php

namespace App\Observers;

use App\Models\DailyPricing;

class DailyPricingObserver
{
    /**
     * @param DailyPricing $pricing
     * @todo replace with a formula from Toni/Cat
     */
    public function saving(DailyPricing $pricing)
    {
        $pricing->is_viable = ($pricing->product->unit_price_gbp * 1.3) < $pricing->suggested_price;
    }
}
