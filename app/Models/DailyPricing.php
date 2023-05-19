<?php

namespace App\Models;

use App\Models\Base\DailyPricing as BaseDailyPricing;
use Carbon\Carbon;

/**
 * @property-read Carbon $last_checked_at
 * @property-read Carbon $last_approved_at
 * @property-read DailyPricing[] $pricing_history
 *
 * Class DailyPricing
 * @package App\Models
 */
class DailyPricing extends BaseDailyPricing
{
	protected $fillable = [
		'submitted_at',
		'submitter_id',
		'product_id',
		'country_id',
		'suggested_price',
		'fba_fee',
		'approved_at',
		'approver_id',
		'is_viable',
		'margin',
		'profit'
	];

    public function product_pricings()
    {
        return $this->hasMany(ProductPricing::class, 'product_id', 'product_id');
    }

	public function getLastCheckedAtAttribute()
    {
        $pricing =  DailyPricing::query()
            ->where('country_id', $this->country->id)
            ->where('product_id', $this->product->id)
            ->orderByDesc('submitted_at')
            ->first();

        return $pricing?$pricing->submitted_at:null;
    }

    public function getLastApprovedAtAttribute(){
	    $pricing = DailyPricing::query()
            ->where('id', '!=', $this->id)
            ->where('country_id', $this->country->id)
            ->where('product_id', $this->product->id)
            ->where('id', '<', $this->id)
            ->whereNotNull('approved_at')
            ->orderByDesc('approved_at')
            ->first();

	    return $pricing?$pricing->approved_at:null;
    }

    public function getPricingHistoryAttribute()
    {
        return DailyPricing::query()
            ->where('country_id', $this->country->id)
            ->where('product_id', $this->product->id)
            ->orderByDesc('submitted_at')
            ->where('id', '<', $this->id)
            ->take(6)
            ->get();
    }

    public function product()
    {
        return parent::product()->withTrashed();
    }

    public function getLastEstLandedCostAttribute()
    {
        if(!$this->product->current_product_pricing) {
            return $this->product->unit_price_gbp;
        }

	    return $this->product->current_product_pricing->unit_price_gbp;
    }


    public function getLocalProfitAttribute()
    {
	    if(!$this->profit){
	        return null;
        }

	    if($this->country->iso_2_code === 'SE' || $this->country->iso_2_code === 'PL') {
	        return $this->profit;
        }

	    return round($this->profit * $this->country->currency->currency_fx_rate, 2);
    }

    public function recalculteProfitAndMargin()
    {
        $vatRate = 0.2;
        $shippingCost = 0;

        /** @var ProductPricingCountry $productPricingCountry */
        $currentProductPricing = $this
            ->product
            ->current_product_pricing;

        if ($currentProductPricing) {
            $productPricingCountry = $currentProductPricing->product_pricing_countries()
                ->where('country_id', $this->country->id)
                ->first();

            if ($productPricingCountry) {
                $vatRate = $productPricingCountry->vat_rate/100;
                $shippingCost = $productPricingCountry->unit_fba_shipping;
            }
        }

        $vat            = $this->suggested_price * $vatRate;
        $commission     = $this->suggested_price * 0.15;
        $fbaFee         = $this->fba_fee;
        $localUnitCost  = $this->product->final_cost_gbp * $this->country->currency->currency_fx_rate;

        $costOfSale = round($localUnitCost + $fbaFee + $commission + $vat + $shippingCost, 2);
        $this->profit = $this->suggested_price - $costOfSale;
        $this->margin = round( $this->profit / $this->suggested_price, 4) * 100;
        if ($this->profit <= 0) {
            $this->margin = 0;
        }
    }
}