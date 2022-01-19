<?php

namespace App\Services;

use App\Domain\LandedCostCalculator;
use App\Exceptions\ZeroUnitsPerCaseException;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * @param Product $product
     * @return void
     */
    public function updateEstimatedLandedCost(Product $product, $save = false)
    {
        if ($product->units_per_case < 1) {
            throw new ZeroUnitsPerCaseException();
        }

        $calculator = new LandedCostCalculator(
            setting('shipping_cost_per_cubic_foot')
        );

        try {
            $shipping = $calculator->calculateWithDuties(
                $product->case_cubic_ft,
                $product->units_per_case,
                $product->unit_price,
                $product->is_excluded_from_commission
            );
        } catch (\Exception $ex) {
            Log::channel('elc')->critical("Failed to calculate change to ELC: " . $ex->getMessage());
            throw $ex;
        }

        $product->est_landed_cost_gbp = round($shipping['est_landed_cost_per_unit_gbp'], 2);
        if ($save) {
            $product->save();
        }
    }
}
