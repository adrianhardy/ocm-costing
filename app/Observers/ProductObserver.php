<?php

namespace App\Observers;

use App\Domain\LandedCostCalculator;
use App\Domain\SortedArticleNumberGenerator;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DailyPricing;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Services\ProductService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductObserver
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function saving(Product $product)
    {

        if($product->id) {
            foreach (Country::query()->where('has_pricing', true)->get() as $country) {
                $hasDailyPricing = DailyPricing::query()
                    ->where('country_id', $country->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if(!$hasDailyPricing) {
                    $dailyPricing = new DailyPricing();
                    $dailyPricing->country()->associate($country);
                    $dailyPricing->product()->associate($product);
                    $dailyPricing->submitted_at = Carbon::now();
                    $dailyPricing->save();
                }
            }
        } else {
            $product->sorted_article_number = SortedArticleNumberGenerator::convert($product->article_number);
        }


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

        if($product->isDirty('est_landed_cost_gbp')) {
            $pricing = ProductPricing::query()
                ->where('product_id', $product->id)
                ->whereNull('superseded_at')
                ->first();

            if($pricing) {
                $pricing->est_landed_cost_per_unit_gbp = $product->est_landed_cost_gbp;
                $pricing->save();
            }
        }
    }
}
