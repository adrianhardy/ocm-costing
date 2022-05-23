<?php

namespace App\Models;

use App\Models\Base\Currency as BaseCurrency;

/**
 * Class Currency
 * @package App\Models
 *
 * @property $currency_fx_rate
 */




class Currency extends BaseCurrency
{
	protected $fillable = [
		'code',
		'name',
		'default_fx_to_gbp',
        'currency_fx_rate'
	];

    public function setCurrencyFxRateAttribute($value) {
        $this->attributes['default_fx_to_gbp'] = 1/$value;
    }

    public function getCurrencyFxRateAttribute() {
        return round(1/$this->default_fx_to_gbp, 2);
    }
}
