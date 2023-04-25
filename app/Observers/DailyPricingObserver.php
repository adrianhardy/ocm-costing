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

        if($pricing->suggested_price && $pricing->fba_fee) {
            $pricing->profit = ($pricing->suggested_price - $pricing->fba_fee - $pricing->product->final_cost_gbp) * $pricing->country->currency->currency_fx_rate;
            $pricing->margin = round($pricing->product->final_cost_gbp / $pricing->profit, 4) * 100;


            if($pricing->product->custom_minimum_profit) {
                $minimumProfit = $pricing->product->custom_minimum_profit;
            } else {
                $minimumProfit = 2.50;
                switch(true) {
                    case $pricing->product->final_cost_gbp <= 9.99:
                        $minimumProfit = 2.50;
                        break;
                    case $pricing->product->final_cost_gbp <= 19.99:
                        $minimumProfit = 6;
                        break;
                    case $pricing->product->final_cost_gbp > 20:
                        $minimumProfit = 10;
                        break;
                }
            }

            $pricing->is_viable = $pricing->profit >= $minimumProfit;
        } else {
            $pricing->is_viable = false;
        }
    }
}
