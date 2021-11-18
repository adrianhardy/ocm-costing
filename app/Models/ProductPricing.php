<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

class ProductPricing extends BaseProductPricing
{
	protected $fillable = [
		'product_id',
		'est_landed_cost_per_unit_gbp',
		'marketplace_commission_pc',
		'unit_shipping_gbp',
		'unit_price_gbp',
		'gross_unit_cost_gbp',
		'approved_at',
		'approver_id',
		'superseded_at',
		'marketplace_fee',
		'sales_order_line_id',
		'uk_vat',
		'fr_vat',
		'de_vat',
		'it_vat',
		'nl_vat',
		'es_vat',
        'au_vat',
        'se_vat',
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
        'unit_se_shipping',
        'unit_usd_shipping',
        'unit_cad_shipping',
        'unit_mxn_shipping',
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


    public function getCalculatedGrossProfitGbpAttribute()
    {
        $profit = $this->unit_price_gbp;
        $profit -= ($this->unit_price_gbp * ($this->marketplace_commission_pc / 100));
        $profit -= $this->marketplace_fee;
        $profit -= $this->est_landed_cost_per_unit_gbp;
        $profit -= $this->unit_shipping_gbp;
        return $profit;
    }

    public function getCalculatedGbpMarginAttribute()
    {

    }

}
