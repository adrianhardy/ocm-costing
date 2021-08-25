<?php

namespace App\Models;

use App\Models\Base\Supplier as BaseSupplier;

class Supplier extends BaseSupplier
{
	protected $fillable = [
		'name',
        'country',
        'delivery_address',
        'contact_name',
        'contact_email',
        'contact_phone'
	];
}
