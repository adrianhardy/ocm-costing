<?php

namespace App\Models;

use App\Models\Base\Currency as BaseCurrency;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Currency
 * @package App\Models
 *
 * @property $currency_fx_rate
 * @property $pricing_fx_rate
 * @property-read $current_fx_rate return pricing if we have it, if not currency
 * @property-read $current_fx_to_gbp as above, but return the inverse amt
 */
class Currency extends BaseCurrency
{
    use LogsActivity;

    protected static $logAttributes = [
        'code',
        'pricing_fx_rate',
        'currency_fx_rate'
    ];

	protected $fillable = [
		'code',
		'name',
		'default_fx_to_gbp',
        'pricing_fx_to_gbp',
        'currency_fx_rate',
        'pricing_fx_rate'
	];

    public function setCurrencyFxRateAttribute($value)
    {
        $this->attributes['default_fx_to_gbp'] = 1/$value;
    }

    public function getCurrencyFxRateAttribute() {
        return round(1/$this->default_fx_to_gbp, 2);
    }

    public function setPricingFxRateAttribute($value)
    {
        $this->attributes['pricing_fx_to_gbp'] = 1/$value;
    }

    public function getPricingFxRateAttribute() {

        if ($this->pricing_fx_to_gbp == null || $this->pricing_fx_to_gbp == 0) {
            return 0;
        }

        return round(1/$this->pricing_fx_to_gbp, 2);
    }


    public function getCurrentFxRateAttribute()
    {
        if ($this->pricing_fx_to_gbp > 0) {
            return $this->pricing_fx_rate;
        }
        // return the inverse of default_fx_to_gbp
        return $this->currency_fx_rate;
    }

    /**
     * As above, but returning the 'inverse'
     * @return mixed
     */
    public function getCurrentFxToGbpAttribute()
    {
        if ($this->pricing_fx_to_gbp > 0) {
            return $this->pricing_fx_to_gbp;
        }
        return $this->default_fx_to_gbp;
    }


}
