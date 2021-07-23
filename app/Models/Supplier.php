<?php

namespace App\Models;

use App\Models\Base\Supplier as BaseSupplier;

class Supplier extends BaseSupplier
{
	protected $fillable = [
		'name'
	];
}
