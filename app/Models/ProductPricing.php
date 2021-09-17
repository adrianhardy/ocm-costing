<?php

namespace App\Models;

use App\Models\Base\ProductPricing as BaseProductPricing;

class ProductPricing extends BaseProductPricing
{
	protected $fillable = [
		'product_id',
		'est_landed_cost_per_unit_gbp',
        'marketplace_commission_pc',
        'suggested_unit_price_gbp',
        'unit_shipping_gbp',
        'unit_price_gbp',
        'gross_unit_cost_gbp',
        'margin_pc',
		'approved_at',
        'superseded_at',
		'approver_id',
        'marketplace_fee'
	];
}
