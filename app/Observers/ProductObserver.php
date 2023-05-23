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

    /**
     * Always make sure products have daily pricing as soon as they're created.
     *
     * 1. Daily pricing is needed from the get-go
     * 2. Pricing (where we price all 30 countries in one go) happens after invoicing
     *
     * @param Product $product
     * @return void
     * @throws \Exception
     */
    public function saving(Product $product)
    {

        // this should probably be done on 'saved', however, there are a few hacks
        // dotted around the system that trigger a touch() just to make sure this
        // kicks in. Not worth it now. The better approach would've been to hook into
        // saved()....
        if($product->id) {
            foreach (Country::query()->where('has_pricing', true)->get() as $country) {
                $hasDailyPricing = DailyPricing::query()
                    ->where('country_id', $country->id)
                    ->where('product_id', $product->id)
                    ->exists();

                // Let's make sure there's an empty daily pricing, which means it'll show in the
                // DailyPricing list, which means it can be updated.
                if(!$hasDailyPricing) {
                    $dailyPricing = new DailyPricing([
                        'submitted_at'      => Carbon::now(),
                        'suggested_price'   => 0,
                        'fba_fee'           => 0,
                        'is_viable'         => false,
                        'margin'            => 0,
                        'profit'            => 0
                    ]);
                    $dailyPricing->country()->associate($country);
                    $dailyPricing->product()->associate($product);
                    $dailyPricing->save();
                }
            }
        } else {
            $product->sorted_article_number = SortedArticleNumberGenerator::convert($product->article_number);
        }

        // if we change anything in a china product that would impact
        // the shipping cost, update our estimated landed cost
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

            $product->unit_price_gbp = $product->est_landed_cost_gbp;
        }
    }
}
