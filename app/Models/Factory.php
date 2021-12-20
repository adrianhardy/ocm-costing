<?php

namespace App\Models;

use App\Models\Base\Factory as BaseFactory;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Factory extends BaseFactory implements HasMedia
{
    use HasMediaTrait;

	protected $fillable = [
		'name',
		'delivery_address',
		'contact_name',
		'contact_email',
		'contact_phone',
		'country',
        'shipping_name',
        'products_description'
	];
}
