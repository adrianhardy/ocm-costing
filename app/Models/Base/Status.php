<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * 
 * @property int $id
 * @property string $name
 * @property string|null $reason
 * @property string $model_type
 * @property int $model_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Status extends Model
{
	protected $table = 'statuses';

	protected $casts = [
		'model_id' => 'int'
	];
}
