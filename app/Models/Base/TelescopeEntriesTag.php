<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TelescopeEntry;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TelescopeEntriesTag
 * 
 * @property string $entry_uuid
 * @property string $tag
 * 
 * @property TelescopeEntry $entry
 *
 * @package App\Models\Base
 */
class TelescopeEntriesTag extends Model
{
	protected $table = 'telescope_entries_tags';
	public $incrementing = false;
	public $timestamps = false;

	public function entry()
	{
		return $this->belongsTo(TelescopeEntry::class, 'entry_uuid', 'uuid');
	}
}
