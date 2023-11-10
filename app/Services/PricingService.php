<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductPricingCountry;
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
     * @param null $unitPriceGbp set if it's a UK product
     * @param null $pricingPriority set if it's a UK product
     */
    public function sendToPricing($productOrSalesOrderLine, $unitPriceGbp = null, $pricingPriority = null)
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

        // calling touch() b/c we need to generate daily pricing for the product
        $product->touch();

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
            if ($line->unit_price_gbp) {
                $costingData['est_landed_cost_per_unit_gbp'] = $line->unit_price_gbp;
            } elseif ($line->product->unit_price_gbp) {
                $costingData['est_landed_cost_per_unit_gbp'] = $line->product->unit_price_gbp;
            } else {
                $costingData = $line->worst_estimated_landed_cost_data;
            }
        } else {
            if ($product->current_landed_cost_per_unit_gbp) {
                $costingData = [
                    'est_landed_cost_per_unit_gbp' => $product->current_landed_cost_per_unit_gbp
                ];
            } else if($product->est_landed_cost_gbp) {
                $costingData = [
                    'est_landed_cost_per_unit_gbp' => $product->est_landed_cost_gbp
                ];
            } else if($product->unit_price_gbp) {
                $costingData = [
                    'est_landed_cost_per_unit_gbp' => $product->unit_price_gbp
                ];
            }
        }

        abort_unless(
            isset($costingData['est_landed_cost_per_unit_gbp']),
            400,
            'Could not find suitable landed cost or unit price for ' . $product->item_number
        );

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

        // Cat will price based on "estimated landed cost" normally, but sometimes
        // there won't be any shipping data with this product, so in that case we'll
        // just say that the cost to OCM = $unitPriceGbp
        // for UK products, just set the ELC/Cost to $unitPriceGbp
        if ($unitPriceGbp && $pricingPriority) {
            $costingData['est_landed_cost_per_unit_gbp'] = $unitPriceGbp;
            $grossCost = $unitPriceGbp;
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

        // support UK-centric products by keeping the price up to date
        // the only reason china lags behind is because of import/shipping
        // But with UK we get a price immediately from the invoice
        if($unitPriceGbp) {
            // AH : I don't know what the significance of unit_price_gbp
            // is on the ProductPricing model - how is this different to
            // est_landed_cost_gbp ?
            $pricing->unit_price_gbp = $unitPriceGbp;
        }

        if($pricingPriority) {
            $pricing->priority = $pricingPriority;
        } else {
            $pricing->priority = ProductPricing::PRIORITY_MEDIUM;
        }

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
