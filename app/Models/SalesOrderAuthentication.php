<?php

namespace App\Models;

use App\Models\Base\SalesOrderAuthentication as BaseSalesOrderAuthentication;

class SalesOrderAuthentication extends BaseSalesOrderAuthentication
{
	protected $hidden = [
		'password',
		'token'
	];

	protected $fillable = [
		'expires_at',
		'consumed_at',
		'password',
		'token',
		'user_id',
		'sales_order_id'
	];
}
