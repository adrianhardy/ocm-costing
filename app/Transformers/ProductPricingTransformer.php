<?php

namespace App\Transformers;

use App\Models\ProductPricing;
use League\Fractal\TransformerAbstract;

class ProductPricingTransformer extends TransformerAbstract
{

    protected $moneyFields = [
        'unit_fr_price',
        'unit_de_price',
        'unit_it_price',
        'unit_nl_price',
        'unit_es_price',
        'unit_se_price',
        'unit_sf_uk_price',
        'unit_sf_fr_price',
        'unit_sf_fr_price',
        'unit_sf_de_price',
        'unit_sf_it_price',
        'unit_sf_nl_price',
        'unit_sf_es_price',
        'unit_sf_au_price',
        'unit_sf_se_price',
        'unit_fr_shipping',
        'unit_de_shipping',
        'unit_it_shipping',
        'unit_nl_shipping',
        'unit_es_shipping',
        'unit_usd_shipping',
        'unit_cad_shipping',
        'unit_mxn_shipping',
        'unit_se_shipping',
        'unit_aud_shipping',
        'unit_sf_usd_price',
        'unit_sf_cad_price',
        'unit_sf_mxn_price',
        'unit_sf_aud_price',
        'unit_usd_price',
        'unit_cad_price',
        'unit_mxn_price',
        'unit_aud_price',
        'eur_fx_rate',
        'usd_fx_rate',
        'cad_fx_rate',
        'mxn_fx_rate',
        'aud_fx_rate'
    ];

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
        'product', 'approver', 'sales_order_line', 'selected_for_export'
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
            'unit_shipping_gbp',
            'unit_price_gbp',
            'gross_unit_cost_gbp',
            'margin_pc',
            'approved_at',
            'superseded_at',
            'uk_vat',
            'fr_vat',
            'de_vat',
            'it_vat',
            'nl_vat',
            'es_vat',
            'se_vat',
            'au_vat',
            'unit_fr_price',
            'unit_de_price',
            'unit_it_price',
            'unit_nl_price',
            'unit_es_price',
            'unit_se_price',
            'unit_sf_uk_price',
            'unit_sf_fr_price',
            'unit_sf_fr_price',
            'unit_sf_de_price',
            'unit_sf_it_price',
            'unit_sf_nl_price',
            'unit_sf_es_price',
            'unit_sf_au_price',
            'unit_sf_se_price',
            'unit_fr_shipping',
            'unit_de_shipping',
            'unit_it_shipping',
            'unit_nl_shipping',
            'unit_es_shipping',
            'unit_usd_shipping',
            'unit_cad_shipping',
            'unit_mxn_shipping',
            'unit_se_shipping',
            'unit_aud_shipping',
            'unit_sf_usd_price',
            'unit_sf_cad_price',
            'unit_sf_mxn_price',
            'unit_sf_aud_price',
            'unit_usd_price',
            'unit_cad_price',
            'unit_mxn_price',
            'unit_aud_price',
            'eur_fx_rate',
            'usd_fx_rate',
            'cad_fx_rate',
            'mxn_fx_rate',
            'aud_fx_rate'
        ]);

        foreach ($this->moneyFields as $field) {
            $json[$field] = floatval(number_format($json[$field], 2, '.', ''));
        }

        return $json;

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
