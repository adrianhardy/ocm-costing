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

	public function getLastCheckedAtAttribute()
    {
        $pricing =  DailyPricing::query()
            ->where('id', '!=', $this->id)
            ->where('country_id', $this->country->id)
            ->where('product_id', $this->product->id)
            ->where('submitted_at', '<', $this->submitted_at)
            ->orderByDesc('submitted_at')
            ->first();

        return $pricing?$pricing->submitted_at:null;
    }

    public function getLastApprovedAtAttribute(){
	    $pricing = DailyPricing::query()
            ->where('id', '!=', $this->id)
            ->where('country_id', $this->country->id)
            ->where('product_id', $this->product->id)
            ->where('approved_at', '<', $this->submitted_at)
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
            ->take(6)
            ->get();
    }

    public function product()
    {
        return parent::product()->withTrashed();
    }


}