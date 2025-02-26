<?php

namespace App\Models;

use App\Domain\ProductPricingViabilityChecker;
use App\Models\Base\DailyPricing as BaseDailyPricing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @property-read Carbon $last_checked_at
 * @property-read Carbon $last_approved_at
 * @property-read DailyPricing[] $pricing_history
 * @property-read float $profit_gbp
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

    public function getProfitGbpAttribute()
    {
        return round($this->profit / $this->country->currency->currency_fx_rate, 2);
    }


    public function getLocalProfitAttribute()
    {
        if(!$this->profit) {
            return null;
        }

        // for SE and PL, return GBP - GBP is considered "local"
        // So normally, $this->profit is expressed in the local currency,
        // but specifically for PL and SE, we convert it back
        if($this->country->iso_2_code === 'SE' || $this->country->iso_2_code === 'PL') {
            return round($this->profit / $this->country->currency->currency_fx_rate, 2);
        }

        return round($this->profit, 2);
    }

    public function recalculteProfitAndMargin($debug = false)
    {
        $vatRate = 0.2;
        $shippingCost = 0;

        if ($this->country) {
            $vatRate = $this->country->vat_rate/100;
        }

        /** @var ProductPricingCountry $productPricingCountry */
        $currentProductPricing = $this
            ->product
            ->current_product_pricing;

        // Products may not have daily pricing, so we won't know their "unit_fba_shipping"
        // amount. This can mean that products get triggered as being viable before we
        // know the full cost of shipping them.
        if ($currentProductPricing) {
            $productPricingCountry = $currentProductPricing->product_pricing_countries()
                ->where('country_id', $this->country->id)
                ->first();

            if ($productPricingCountry) {
                $shippingCost = $productPricingCountry->unit_fba_shipping;
            }
        }

        $vat            = $this->suggested_price * $vatRate;
        $commission     = $this->suggested_price * 0.15;
        $fbaFee         = $this->fba_fee;
        $localUnitCost  = $this->product->unit_price_gbp;

        $fbaDigitalTax = $fbaFee * (floatval(setting('amazon_digital_tax_pc', 0)) / 100);
        $fbaDigitalTax += $commission * (floatval(setting('amazon_digital_tax_pc', 0)) / 100);
        $fbaDigitalTax = round($fbaDigitalTax, 2);

        if ($localUnitCost === null) {
            $localUnitCost = $this->product->current_landed_cost_per_unit_gbp;
        }

        if ($localUnitCost === null) {
            $localUnitCost = $this->product->est_landed_cost_gbp;
        }

        if ($localUnitCost === null || !($localUnitCost > 0)) {
            throw new \RuntimeException("Cannot calculate profit without unit_price_gbp, current_landed_cost_per_unit_gbp, est_landed_cost_gbp");
        }

        if ($this->country->currency->current_fx_rate != '1.00') {
            $localUnitCost = $localUnitCost * $this->country->currency->current_fx_rate;
        }

        // $costOfSale = round($localUnitCost + $fbaFee + $commission + $vat + $shippingCost, 2);
        // CC: 4th December 2023:
        // The correct formula should be;
        // Price to sell for - vat - commision - fba fee - cost price (in local currency)
        // Since 22/Oct/24 - take fbaDigitalTax off to when determining profit
        $this->profit = round(
            $this->suggested_price -
            $vat -
            $commission -
            $fbaFee -
            $localUnitCost -
            $shippingCost -
            $fbaDigitalTax, 2);

        $this->margin = round( $this->profit / $localUnitCost, 4) * 100;

        if ($this->profit <= 0) {
            $this->margin = 0;
        }
    }

    /**
     * Specifically, we do not save the changes here because DailyPricingController
     * wants to have the viability as a "preview"
     *
     * @return void
     */
    public function updateViability()
    {

        $prefix = "[" . class_basename(__METHOD__) . "][Product {$this->product_id}]";
        Log::channel('pricing')->debug(
            $prefix .
            " Now using fx rate {$this->country->currency->current_fx_rate}, instead of old" .
            " rate which would've been {$this->country->currency->currency_fx_rate}"
        );

        $viability = new ProductPricingViabilityChecker();
        $this->recalculteProfitAndMargin();
        $gbpProfit = round($this->profit / $this->country->currency->current_fx_rate, 2);

        $unitPriceGbp = $this->product->unit_price_gbp;

        // as of 12/Jul/2024, we're using the HIGHEST EVER product cost.
        // we need to filter that by this pricing's country.
        $highestUnitPriceGbp = $this->product
            ->product_pricings_product()
            ->whereHas('product_pricing_countries', function($query) {
                $query->where('id', $this->country->id);
            })
            ->orderBy('unit_price_gbp', 'DESC')
            ->first();

        $this->is_viable = $viability->isViable(
            $gbpProfit,
            max($highestUnitPriceGbp, $unitPriceGbp),
            $this->product->custom_minimum_profit
        );
    }


    /**
     * Only look at the latest, most recent submissions to daily pricing
     * @param $query
     * @return mixed
     */
    public function scopeWhereLatestSubmission($query)
    {
        $subQuery = DB::table('daily_pricing AS dp')
            ->addSelect(DB::raw('MAX(dp.id) as id'))
            ->addSelect('dp.product_id')
            ->addSelect('dp.country_id')
            // for debugging:
            // ->addSelect(DB::raw('MAX(submitted_at) as submitted_at'))
            // ->addSelect(DB::raw('GROUP_CONCAT(id)'))
            // ->addSelect(DB::raw('GROUP_CONCAT(submitted_at)'))
            ->whereNotNull('dp.submitted_at')
            ->groupBy('dp.country_id')
            ->groupBy('dp.product_id')
            ->orderByRaw('MAX(submitted_at) DESC');

        return $query->joinSub(
            $subQuery,
            'latest_daily_pricing',
            'latest_daily_pricing.id',
            '=',
            'daily_pricing.id'
        );
    }
}
