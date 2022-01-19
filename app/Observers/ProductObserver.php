<?php

namespace App\Observers;

use App\Domain\LandedCostCalculator;
use App\Models\Currency;
use App\Models\Product;
use App\Services\ProductService;

class ProductObserver
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

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
            'unit_price',
            'is_excluded_from_commission'
        ]) || !$product->est_landed_cost_gbp) {

            if ($product->units_per_case > 0) {
                $this->productService->updateEstimatedLandedCost($product);
            }
        }
    }
}
