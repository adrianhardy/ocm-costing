<?php

namespace App\Models;

use App\Models\Base\ImportMapping as BaseImportMapping;

class ImportMapping extends BaseImportMapping
{
	protected $fillable = [
		'name',
		'type',
		'mapping'
	];
}
