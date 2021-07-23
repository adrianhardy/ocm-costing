<?php

namespace App\Models;

use App\Models\Base\TelescopeEntry as BaseTelescopeEntry;

class TelescopeEntry extends BaseTelescopeEntry
{
	protected $fillable = [
		'uuid',
		'batch_id',
		'family_hash',
		'should_display_on_index',
		'type',
		'content'
	];
}
