<?php

namespace App\Models;

use App\Models\Base\Consignment as BaseConsignment;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * @property-read float $total_calculated_cubic_m
 * @property-read float $total_calculated_cubic_ft
 */
class Consignment extends BaseConsignment implements HasMedia
{
    use HasMediaTrait;
    use SoftDeletes;

    public function registerMediaCollections()
    {
        $this->addMediaCollection('default');
    }

    public function getTotalCalculatedCubicMAttribute()
    {
        $total = 0;
        foreach ($this->sales_order_lines as $line) {
            $total += round($line->case_cubic_m * ($line->pivot->qty_shipped / $line->units_per_case), 4);
        }
        return $total;
    }

    public function getTotalCalculatedCubicFtAttribute()
    {
        $total = 0;
        foreach ($this->sales_order_lines as $line) {
            $total += round($line->case_cubic_ft * ($line->pivot->qty_shipped / $line->units_per_case), 4);
        }
        return $total;
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
