<?php

namespace App\Observers;

use App\Models\DailyPricing;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;

class DailyPricingObserver
{
    /**
     * @param DailyPricing $pricing
     * @todo replace with a formula from Toni/Cat
     */
    public function saving(DailyPricing $pricing)
    {

        if($pricing->suggested_price && $pricing->fba_fee) {

            $vatRate = 20;
            $shippingCost = 0;

            /** @var ProductPricingCountry $productPricingCountry */
            $currentProductPricing = $pricing
                ->product
                ->current_product_pricing;

            if ($currentProductPricing) {
                $productPricingCountry = $currentProductPricing->product_pricing_countries()
                    ->where('country_id', $pricing->country->id)
                    ->first();

                if ($productPricingCountry) {
                    $vatRate = $productPricingCountry->vat_rate/100;
                    $shippingCost = $productPricingCountry->unit_fba_shipping;
                }
            }

            $vat            = $pricing->suggested_price * ($vatRate/100);
            $commission     = $pricing->suggested_price * 0.15;
            $fbaFee         = $pricing->fba_fee;
            $localUnitCost  = $pricing->product->final_cost_gbp * $pricing->country->currency->currency_fx_rate;

            $costOfSale = round($localUnitCost + $fbaFee + $commission + $vat + $shippingCost, 2);
            $pricing->profit = $pricing->suggested_price - $costOfSale;
            $pricing->margin = round($costOfSale / $pricing->suggested_price, 4) * 100;

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
