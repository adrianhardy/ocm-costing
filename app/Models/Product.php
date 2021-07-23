<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Product extends BaseProduct implements HasMedia
{
    use HasMediaTrait;

	protected $fillable = [
		'item_number',
		'article_number',
		'name',
		'packing_instructions',
		'barcode'
	];
}
