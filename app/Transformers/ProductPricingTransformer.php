<?php

namespace App\Transformers;

use App\Models\Country;
use App\Models\ProductPricing;
use App\Models\ProductPricingCurrency;
use League\Fractal\TransformerAbstract;

class ProductPricingTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'product', 'approver', 'sales_order_line', 'selected_for_export', 'countries'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ProductPricing $pricing)
    {
        $json = $pricing->only([
            'id',
            'created_at',
            'updated_at',
            'est_landed_cost_per_unit_gbp',
            'marketplace_commission_pc',
            'suggested_unit_price_gbp',
            'gross_unit_cost_gbp',
            'margin_pc',
            'submitted_at',
            'approved_at',
            'superseded_at',
            'eur_fx_rate',
            'current_eur_fx_rate'
        ]);

        return $json;
    }

    public function includeCountries(ProductPricing $pricing)
    {
        /** @var Country[] $countries */
        $countries = Country::query()
            ->where('has_pricing', '1')
            ->get();

        $json = [];

        foreach ($countries as $country) {
            $code = strtolower($country->iso_2_code);
            $countryPrice = $pricing
                ->product_pricing_countries()
                ->where('country_id', $country->id)
                ->first();

            if ($countryPrice) {
                $json[$code] = $countryPrice->only([
                    'unit_fba_fee',
                    'unit_fba_price',
                    'unit_sf_price',
                    'unit_sf_shipping',
                    'unit_fba_shipping',
                    'vat_rate',
                    'gbp_fx_rate',
                    'current_gbp_fx_rate'
                ]);
            } else {

            }
        };
        return $this->primitive($json);
    }

    /**
     * @param ProductPricing $productPricing
     * @return \League\Fractal\Resource\Primitive
     */
    public function includeSelectedForExport(ProductPricing $productPricing)
    {
        return $this->primitive(true);
    }

    public function includeSalesOrderLine(ProductPricing $productPricing)
    {
        if (!$productPricing->sales_order_line) {
            return $this->null();
        }
        return $this->item($productPricing->sales_order_line, new SalesOrderLineTransformer());
    }

    public function includeApprover(ProductPricing $productPricing)
    {
        if (!$productPricing->approver_id) {
            return $this->null();
        }
        return $this->item($productPricing->approver, new UserTransformer());
    }

    public function includeProduct(ProductPricing $pricing){
        if(!$pricing->product_id){
            return $this->null();
        }
        $transformer = new ProductTransformer();
        return $this->item($pricing->product, $transformer);
    }
}
