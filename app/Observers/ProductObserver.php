<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        if ($product->isDirty([
            'length_m',
            'width_m',
            'height_m',
            'length_cm',
            'width_cm',
            'height_cm',
            'units_per_case',
            'case_weight_kg',
            'unit_cost',
            'is_excluded_from_commission'
        ]) || !$product->est_landed_cost_gbp) {

            if ($product->units_per_case > 0) {
                /** @var Currency $usd */
                $usd = Currency::query()->where('code', 'USD')->first();
                $shippingCostUsd = (setting('shipping_cost_per_cubic_foot') / $usd->default_fx_to_gbp);

                $commissionUplift = 1 + (setting('default_commission_rate_pc')/100);
                $importDutyUplift = 1 + (setting('default_import_duty_pc')/100);

                $importCostUsd = $product->unit_price  * $importDutyUplift;
                $costToShipUsd = ($importCostUsd + ($product->case_cubic_ft * $shippingCostUsd)) / $product->units_per_case;

                if (!$product->is_excluded_from_commission) {
                    $costToShipUsd = $costToShipUsd * $commissionUplift;
                }

                $product->est_landed_cost_gbp = round($costToShipUsd * $usd->default_fx_to_gbp, 2);
            }
        }
    }
}
