<?php

namespace App\Observers;

use App\Domain\ProductPricingViabilityChecker;
use App\Models\DailyPricing;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DailyPricingObserver
{
    /**
     * @param DailyPricing $pricing
     * @todo replace with a formula from Toni/Cat
     */
    public function saving(DailyPricing $pricing)
    {

        $prefix = "[" . class_basename(__METHOD__) . "][Product {$pricing->product_id}]";

        if($pricing->suggested_price && $pricing->fba_fee) {
            $pricing->updateViability();
        } else {
            $pricing->is_viable = false;
        }

        if($pricing->is_viable) {
            $pricing->approved_at = Carbon::now();
            $pricing->approver()->associate(auth()->user());


            $factor = $pricing->country->currency->currency_fx_rate;
            // PRICING is entered in local currency, no need for conversion
            $factor = 1; // undo currency conversion below

            // For daily pricing, "ProductPricing" might not yet be set up,
            // so we're checking to see if it is before we attempt to modify:
            if ($pricing->product->current_product_pricing) {
                $pricing->product->current_product_pricing->save();

                /** @var ProductPricingCountry $pricingCountry */
                $pricingCountry = $pricing->product->current_product_pricing->product_pricing_countries()
                    ->where('country_id', $pricing->country_id)->firstOrFail();

                $pricingCountry->unit_fba_price = ($factor * $pricing->suggested_price);
                $pricingCountry->unit_fba_fee = $pricing->fba_fee;
                $pricingCountry->save();
            }
        }


    }
}
