<?php

namespace App\Models;

use App\Models\Base\TelescopeEntriesTag as BaseTelescopeEntriesTag;

class TelescopeEntriesTag extends BaseTelescopeEntriesTag
{
	protected $fillable = [
		'entry_uuid',
		'tag'
	];
}
