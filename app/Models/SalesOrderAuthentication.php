<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class SalesOrderAuthentication
 * @package App\Models
 *
 * @property User $user
 * @property SalesOrder $salesOrder
 * @property string $token
 */
class SalesOrderAuthentication extends Model
{
	protected $hidden = [
		'password',
		'token'
	];

	protected $dates = [
	    'expires_at',
        'consumed_at'
    ];

	protected $fillable = [
		'expires_at',
		'consumed_at',
		'password',
		'token',
		'user_id',
		'sales_order_id'
	];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function getHasExpiredAttribute()
    {
        return $this->expires_at < Carbon::now();
    }

}
