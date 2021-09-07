<?php

namespace App\Models;

use App\Models\Base\Country as BaseCountry;

class Country extends BaseCountry
{
	protected $fillable = [
		'name',
		'iso_2_code',
		'import_duty',
		'export_duty',
		'vat_rate',
		'currency_id'
	];
}
