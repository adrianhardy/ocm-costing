<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Import
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $aborted_at
 * @property Carbon|null $completed_at
 * @property int $creator_id
 * @property int $skip_header_rows
 * @property string $path
 * @property string $type
 * @property int|null $total_rows
 * @property int|null $total_errors
 * @property int|null $current_line
 * @property array $mapping
 * @property array $config
 * @property string $log_path
 * 
 * @property User $creator
 *
 * @package App\Models\Base
 */
class Import extends Model
{
	protected $table = 'imports';

	protected $casts = [
		'creator_id' => 'int',
		'skip_header_rows' => 'int',
		'total_rows' => 'int',
		'total_errors' => 'int',
		'current_line' => 'int',
		'mapping' => 'json',
		'config' => 'json'
	];

	protected $dates = [
		'aborted_at',
		'completed_at'
	];

	public function creator()
	{
		return $this->belongsTo(User::class, 'creator_id');
	}
}
