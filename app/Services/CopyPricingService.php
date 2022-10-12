<?php


namespace App\Services;


use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CopyPricingService
{
    /*
     * Master product is the product that already has pricing,
     * and product is the product that we are copying it to
     */
    public function copy(Product $masterProduct, Product $product) {
        DB::beginTransaction();

        //if product already has pricing, supersede it
        if($product->current_product_pricing) {
            $product->current_product_pricing->superseded_at = now();
            $product->current_product_pricing->save();
        }

        // create pricing from master product
        $newPricing = $product->current_product_pricing->replicate(['superseded_at']);
        $newPricing->save();

        //foreach pricing country on master pricing, copy and associate with product
        foreach($masterProduct->current_product_pricing->product_pricing_countries as $pricingCountry) {
            $copy = $pricingCountry->replicate(['product_pricing_id']);
            $newPricing->product_pricing_countries()->save($copy);
        }

        DB::commit();
        return ($product);
    }

}
