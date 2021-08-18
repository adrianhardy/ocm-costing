<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $code
 * @property string $name
 * @property float|null $default_fx_to_gbp
 * 
 * @property Collection|SalesOrder[] $sales_orders_currency
 *
 * @package App\Models\Base
 */
class Currency extends Model
{
	protected $table = 'currencies';

	protected $casts = [
		'default_fx_to_gbp' => 'float'
	];

	public function sales_orders_currency()
	{
		return $this->hasMany(SalesOrder::class);
	}
}
