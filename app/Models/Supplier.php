<?php

namespace App\Models;

use App\Models\Base\Supplier as BaseSupplier;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Supplier extends BaseSupplier implements HasMedia
{
    use HasMediaTrait;

	protected $fillable = [
		'name',
        'country',
        'delivery_address',
        'contact_name',
        'contact_email',
        'contact_phone'
	];
}
