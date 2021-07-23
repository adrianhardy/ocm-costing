<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TelescopeMonitoring
 * 
 * @property string $tag
 *
 * @package App\Models\Base
 */
class TelescopeMonitoring extends Model
{
	protected $table = 'telescope_monitoring';
	public $incrementing = false;
	public $timestamps = false;
}
