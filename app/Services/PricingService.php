<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
use App\Models\ProductPricingCurrency;
use App\Models\SalesOrderLine;
use Carbon\Carbon;

class PricingService
{
    /**
     * @return Country[]
     */
    protected $pricingCountries = [];

    protected function getPricingCountries()
    {
        if (count($this->pricingCountries) < 1) {
            $this->pricingCountries = Country::query()
                ->where('has_pricing', 1)
                ->get();
        }

        return $this->pricingCountries;
    }

    /**
     * @param $productOrSalesOrderLine
     * @return void
     */
    public function sendToPricing($productOrSalesOrderLine)
    {
        $line = null;
        $product = null;
        if ($productOrSalesOrderLine instanceof SalesOrderLine) {
            $product = $productOrSalesOrderLine->product;
            $line = $productOrSalesOrderLine;
        } elseif ($productOrSalesOrderLine instanceof Product) {
            $product = $productOrSalesOrderLine;
            $line = null;
        } else {
            throw new \InvalidArgumentException("Only accepts either a Product or a SalesOrderLine");
        }

        ProductPricing::query()
            ->whereNull('superseded_at')
            ->whereNull('approved_at')
            ->where('product_id', $product->id)
            ->get()
            ->each(function(ProductPricing $superseded) {
                $superseded->superseded_at = Carbon::now();
                $superseded->save();
            });

        if ($line) {
            $costingData = $line->worst_estimated_landed_cost_data;
        } else {
            $costingData = [
                'est_landed_cost_per_unit_gbp' => $product->est_landed_cost_gbp
            ];
        }

        $suggestedPrice = $costingData['est_landed_cost_per_unit_gbp'] * setting('suggested_price_multiplier');
        $commission = $suggestedPrice * (1/setting('default_marketplace_commission_pc'));
        $grossCost = $costingData['est_landed_cost_per_unit_gbp'] + $commission;

        $pricing = new ProductPricing();
        if ($line) {
            $pricing->sales_order_line()->associate($line);
            $pricing->product()->associate($line->product);
        } else {
            $pricing->product()->associate($product);
        }

        /** @var Currency $eur */
        $eur = Currency::query()->where('code', 'EUR')->first();
        $pricing->fill([
            'est_landed_cost_per_unit_gbp' => $costingData['est_landed_cost_per_unit_gbp'],
            'marketplace_commission_pc' => setting('default_marketplace_commission_pc'),
            'gross_unit_cost_gbp' => $grossCost,
            'margin_pc' => $suggestedPrice / $grossCost,
            'eur_fx_rate' => $eur->default_fx_to_gbp
        ]);
        $pricing->save();
        foreach ($this->getPricingCountries() as $country) {
            // create all empty country-pricing for this
            // particular product pricing

            // EUR rate is snapshotted on top level because it's set
            // once for all countries
            $countryPricing = new ProductPricingCountry([
                'vat_rate' => $country->vat_rate,
                'gbp_fx_rate' => $country->currency->default_fx_to_gbp
            ]);
            $countryPricing->country()->associate($country);
            $pricing->product_pricing_countries()->save($countryPricing);
        }
    }
}
