<?php

namespace App\Models;

use App\Models\Base\Consignment as BaseConsignment;

class Consignment extends BaseConsignment
{
	protected $fillable = [
		'reference',
		'est_cost_cuft_gbp',
		'departed_at',
		'docked_at'
	];
}
