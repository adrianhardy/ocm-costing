<?php

namespace App\Observers;

use App\Models\DailyPricing;

class DailyPricingObserver
{
    public function saving(DailyPricing $pricing){
        $pricing->is_viable = ($pricing->fba_fee * 1.3) < $pricing->suggested_price;
    }
}
