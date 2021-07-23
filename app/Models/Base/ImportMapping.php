<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ImportMapping
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $type
 * @property array $mapping
 *
 * @package App\Models\Base
 */
class ImportMapping extends Model
{
	protected $table = 'import_mappings';

	protected $casts = [
		'mapping' => 'json'
	];
}
