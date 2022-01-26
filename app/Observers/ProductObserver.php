<?php

namespace App\Observers;

use App\Domain\LandedCostCalculator;
use App\Domain\SortedArticleNumberGenerator;
use App\Models\Currency;
use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {

        $product->sorted_article_number = SortedArticleNumberGenerator::convert($product->article_number);


        if ($product->isDirty([
            'length_m',
            'width_m',
            'height_m',
            'length_cm',
            'width_cm',
            'height_cm',
            'units_per_case',
            'case_weight_kg',
            'unit_price',
            'is_excluded_from_commission'
        ]) || !$product->est_landed_cost_gbp) {

            if ($product->units_per_case > 0) {

                $calculator = new LandedCostCalculator(
                    setting('shipping_cost_per_cubic_foot')
                );

                $shipping = $calculator->calculateWithDuties(
                    $product->case_cubic_ft,
                    $product->units_per_case,
                    $product->unit_price,
                    $product->is_excluded_from_commission
                );

                $product->est_landed_cost_gbp = round($shipping['est_landed_cost_per_unit_gbp'], 2);
            }
        }
    }
}
