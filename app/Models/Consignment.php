<?php

namespace App\Models;

use App\Models\Base\Consignment as BaseConsignment;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Consignment extends BaseConsignment implements HasMedia
{
    use HasMediaTrait;

    public function registerMediaCollections()
    {
        $this->addMediaCollection('default');
    }

    protected $fillable = [
		'reference',
		'est_cost_cuft_gbp',
		'departed_at',
		'docked_at',
		'sales_order_id',
		'invoice_amt',
		'creator_id',
		'supplier_ref',
		'our_order_number',
		'vessel_name',
		'container_number',
		'bl_number',
		'shipping_agent_ref',
        'estimated_arrival_at',
        'estimated_departure_at'
	];
}
