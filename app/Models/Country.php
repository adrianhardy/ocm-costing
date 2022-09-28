<?php

namespace App\Models;

use App\Models\Base\Country as BaseCountry;

class Country extends BaseCountry
{
    protected $cachedEuroCurrency = null;

	protected $fillable = [
		'name',
		'iso_2_code',
		'import_duty',
		'export_duty',
		'vat_rate',
		'currency_id'
	];

    // Bulgaria, Croatia, Czech Republic, Denmark,
    // Hungary, Poland, Romania, and Sweden.
    const NON_EURO_EU_COUNTRIES = [
        'PL', 'SK', 'BG', 'HR', 'CZ', 'DK',
        'HU', 'PL', 'RO', 'SE'
    ];

    public function getIsRowAttribute()
    {

        if ($this->uses_euro) {
            return false;
        }

        if (in_array($this->iso_2_code, self::NON_EURO_EU_COUNTRIES)) {
            return false;
        }

        return true;
    }

    public function getUsesEuroAttribute()
    {
        if (!$this->cachedEuroCurrency) {
            $this->cachedEuroCurrency = Currency::query()->where('code', 'EUR')->first();
        }
        return $this->currency_id == $this->cachedEuroCurrency->id;
    }
}
