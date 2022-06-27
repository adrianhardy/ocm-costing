<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\SalesOrderLine;

class PricingService
{
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

        $fr = Country::where('iso_2_code', 'FR')->first();
        $uk = Country::where('iso_2_code', 'GB')->first();
        $de = Country::where('iso_2_code', 'DE')->first();
        $es = Country::where('iso_2_code', 'ES')->first();
        $pl = Country::where('iso_2_code', 'PL')->first();
        $it = Country::where('iso_2_code', 'IT')->first();
        $nl = Country::where('iso_2_code', 'NL')->first();
        $us = Country::where('iso_2_code', 'US')->first();
        $ca = Country::where('iso_2_code', 'CA')->first();
        $mx = Country::where('iso_2_code', 'MX')->first();
        $se = Country::where('iso_2_code', 'se')->first();
        $au = Country::where('iso_2_code', 'au')->first();

        $pricing = new ProductPricing();
        if ($line) {
            $pricing->sales_order_line()->associate($line);
            $pricing->product()->associate($line->product);
        } else {
            $pricing->product()->associate($product);
        }
        $pricing->fill([
            'est_landed_cost_per_unit_gbp' => $costingData['est_landed_cost_per_unit_gbp'],
            'unit_price_gbp' => $suggestedPrice,
            'marketplace_commission_pc' => setting('default_marketplace_commission_pc'),
            'gross_unit_cost_gbp' => $grossCost,
            'margin_pc' => $suggestedPrice / $grossCost,
            'fr_vat' => $fr->vat_rate,
            'uk_vat' => $uk->vat_rate,
            'es_vat' => $es->vat_rate,
            'it_vat' => $it->vat_rate,
            'de_vat' => $de->vat_rate,
            'nl_vat' => $nl->vat_rate,
            'se_vat' => $se->vat_rate,
            'au_vat' => $au->vat_rate,
            'pl_vat' => $pl->vat_rate,
            'mxn_fx_rate' => $mx->currency->default_fx_to_gbp,
            'eur_fx_rate' => $fr->currency->default_fx_to_gbp,
            'usd_fx_rate' => $us->currency->default_fx_to_gbp,
            'cad_fx_rate' => $ca->currency->default_fx_to_gbp,
            'aud_fx_rate' => $au->currency->default_fx_to_gbp,
            'pln_fx_rate' => $pl->currency->default_fx_to_gbp,
            'sek_fx_rate' => $se->currency->default_fx_to_gbp,
            'unit_uk_fba_fee' => 0,
            'unit_se_fba_fee' => 0,
            'unit_fr_fba_fee' => 0,
            'unit_de_fba_fee' => 0,
            'unit_es_fba_fee' => 0,
            'unit_it_fba_fee' => 0,
            'unit_nl_fba_fee' => 0,
            'unit_usd_fba_fee' => 0,
            'unit_cad_fba_fee' => 0,
            'unit_mxn_fba_fee' => 0,
            'unit_aud_fba_fee' => 0,
            'unit_pln_fba_fee' => 0,
            'unit_sek_fba_fee' => 0,
        ]);
        $pricing->save();
    }
}
