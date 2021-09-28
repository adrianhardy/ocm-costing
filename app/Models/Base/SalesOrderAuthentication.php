<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalesOrderAuthentication
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon $expires_at
 * @property Carbon|null $consumed_at
 * @property string|null $password
 * @property string $token
 * @property int $user_id
 * @property int $sales_order_id
 * 
 * @property SalesOrder $sales_order
 * @property User $user
 *
 * @package App\Models\Base
 */
class SalesOrderAuthentication extends Model
{
	protected $table = 'sales_order_authentications';

	protected $casts = [
		'user_id' => 'int',
		'sales_order_id' => 'int'
	];

	protected $dates = [
		'expires_at',
		'consumed_at'
	];

	public function sales_order()
	{
		return $this->belongsTo(SalesOrder::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
