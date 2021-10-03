<?php

namespace App\Models;

use App\Models\Base\SalesOrderAuthentication as BaseSalesOrderAuthentication;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SalesOrderAuthentication extends BaseSalesOrderAuthentication

/**
 * Class SalesOrderAuthentication
 * @package App\Models
 *
 * @property User $user
 * @property SalesOrder $salesOrder
 * @property string $token
 */
{
	protected $fillable = [
		'expires_at',
		'consumed_at',
		'password',
		'token',
		'user_id',
		'sales_order_id'
	];

    /**
     * @return bool
     */
    public function getHasExpiredAttribute()
    {
        return $this->expires_at < Carbon::now();
    }
}
