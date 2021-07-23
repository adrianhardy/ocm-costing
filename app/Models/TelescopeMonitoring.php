<?php

namespace App\Models;

use App\Models\Base\TelescopeMonitoring as BaseTelescopeMonitoring;

class TelescopeMonitoring extends BaseTelescopeMonitoring
{
	protected $fillable = [
		'tag'
	];
}
