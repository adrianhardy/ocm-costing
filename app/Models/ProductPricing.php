<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

class ProductPricing extends BaseProductPricing
{
	protected $fillable = [
		'product_id',
		'est_landed_cost_per_unit_gbp',
		'marketplace_unit_commission_gbp',
		'marketplace_unit_fee_gbp',
		'unit_shipping_gbp',
		'unit_duty_gbp',
		'suggested_price_gbp',
		'price_gbp',
		'approved_at',
		'approver_id'
	];
}
