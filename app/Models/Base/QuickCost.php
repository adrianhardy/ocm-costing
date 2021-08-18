<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class QuickCost
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string $reference
 * @property int|null $user_id
 * @property float $exchange_rate
 * @property float $unit_price
 * @property int $units_per_case
 * @property float $case_width_m
 * @property float $case_height_m
 * @property float $case_depth_m
 * @property float $cost_to_ship_cu_m
 * @property float $commission_rate
 * @property float $import_duty_rate
 * 
 * @property User|null $user
 *
 * @package App\Models\Base
 */
class QuickCost extends Model
{
	use SoftDeletes;
	protected $table = 'quick_costs';

	protected $casts = [
		'user_id' => 'int',
		'exchange_rate' => 'float',
		'unit_price' => 'float',
		'units_per_case' => 'int',
		'case_width_m' => 'float',
		'case_height_m' => 'float',
		'case_depth_m' => 'float',
		'cost_to_ship_cu_m' => 'float',
		'commission_rate' => 'float',
		'import_duty_rate' => 'float'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
