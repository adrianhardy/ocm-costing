<?php

namespace App\Models;

use App\Models\Base\Factory as BaseFactory;

class Factory extends BaseFactory
{
	protected $fillable = [
		'name',
		'delivery_address',
		'contact_name',
		'contact_email',
		'contact_phone',
		'country'
	];
}
