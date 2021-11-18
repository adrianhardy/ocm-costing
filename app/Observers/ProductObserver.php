<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        if ($product->wasChanged([
            'length_m',
            'width_m',
            'height_m',
            'units_per_case',
            'unit_cost',
            'is_excluded_from_commission'
        ]) || !$product->est_landed_cost_gbp) {

            if ($product->units_per_case > 0) {
                /** @var Currency $usd */
                $usd = Currency::query()->where('code', 'USD')->first();
                $shippingCost = $product->case_cubic_ft * setting('shipping_cost_per_cubic_foot_usd');
                $commissionUplift = 1 + (setting('default_commission_rate_pc')/100);
                $importDutyUplift = 1 + (setting('default_import_duty_pc')/100);

                $importCost = $product->unit_price * $importDutyUplift;
                $costToShip = ($importCost + ($product->case_cubic_ft * $shippingCost)) / $product->units_per_case;

                if (!$product->is_excluded_from_commission) {
                    $costToShip = $costToShip * $commissionUplift;
                }

                $product->est_landed_cost_gbp = round($costToShip * $usd->default_fx_to_gbp, 2);
            }
        }
    }
}
