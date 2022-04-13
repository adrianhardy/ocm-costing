<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

/**
 * Class ProductPricing
 * @package App\Models
 * @property $current_eur_fx_rate
 * @property $current_usd_fx_rate
 * @property $current_cad_fx_rate
 * @property $current_mxn_fx_rate
 * @property $current_aud_fx_rate
 */

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
        'pl_vat',
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
        'unit_pln_shipping',
        'unit_sf_usd_price',
        'unit_sf_cad_price',
        'unit_sf_mxn_price',
        'unit_sf_aud_price',
        'unit_sf_pln_price',
		'unit_usd_price',
		'unit_cad_price',
		'unit_mxn_price',
        'unit_aud_price',
        'unit_pln_price',
		'eur_fx_rate',
		'usd_fx_rate',
		'cad_fx_rate',
		'mxn_fx_rate',
        'aud_fx_rate',
        'pln_fx_rate',
        'sek_fx_rate',
        'unit_uk_fba_fee',
        'unit_se_fba_fee',
        'unit_fr_fba_fee',
        'unit_de_fba_fee',
        'unit_es_fba_fee',
        'unit_it_fba_fee',
        'unit_nl_fba_fee',
        'unit_usd_fba_fee',
        'unit_cad_fba_fee',
        'unit_mxn_fba_fee',
        'unit_aud_fba_fee',
        'unit_pln_fba_fee',
	];


	//All of these are neccessary, as you need to save the values before you can autogenerate pricing, and while we
    //we could just change the maths on the vue, it would make the data in the product pricing table inconsistent
    //with the rest of the database when we save them.


	public function getCurrentEurFxRateAttribute()
    {
        return round(1 / $this->eur_fx_rate, 2);
    }

    public function setCurrentEurFxRateAttribute($value)
    {
        $this->attributes['usd_fx_rate'] = round(1 / $value,4);
    }

    public function getCurrentUsdFxRateAttribute()
    {
        return round(1 / $this->eur_fx_rate, 2);
    }

    public function setCurrentUsdFxRateAttribute($value)
    {
        $this->attributes['usd_fx_rate'] = round(1 / $value,4);
    }

    public function getCurrentMxnFxRateAttribute()
    {
        return round(1 / $this->mxn_fx_rate, 2);
    }

    public function setCurrentMxnFxRateAttribute($value)
    {
        $this->attributes['mxn_fx_rate'] = round(1 / $value, 4);
    }

    public function getCurrentCadFxRateAttribute()
    {
        return round(1 / $this->cad_fx_rate,2);
    }

    public function setCurrentCadFxRateAttribute($value)
    {
        $this->attributes['cad_fx_rate'] = round(1 / $value, 4);
    }

    public function getCurrentAudFxRateAttribute()
    {
	    return round(1 / $this->aud_fx_rate, 2);
    }

    public function setCurrentAudFxRateAttribute($value)
    {
        $this->attributes['aud_fx_rate'] = round(1 / $value, 4);
    }

    public function getCurrentSekFxRateAttribute()
    {
        return round(1 / $this->sek_fx_rate, 2);
    }

    public function setCurrentSekFxRateAttribute($value)
    {
        $this->attributes['sek_fx_rate'] = round(1 / $value,4);
    }

    public function getCurrentPlnFxRateAttribute()
    {
        return round(1/ $this->pln_fx_rate, 2);
    }

    public function setCurrentPlnFxRateAttribute($value)
    {
        $this->attributes['pln_fx_rate'] = round(1 / $value, 4);
    }

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
