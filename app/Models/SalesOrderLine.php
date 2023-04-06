<?php

namespace App\Models;

use App\Domain\LandedCostCalculator;
use App\Models\Base\SalesOrderLine as BaseSalesOrderLine;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Models\Media;

/**
 * Class SalesOrderLine
 * @package App\Models
 * @property-read $estimated_shipping_cost_gbp
 * @property-read $worst_estimated_landed_cost_data
 * @property-read $case_cubic_m
 * @property-read $case_cubic_ft
 * @property $total_cubic_ft
 * @property $qty_received
 * @property-read float $live_estimated_landed_cost
 *
 * @property-read int $qty_remaining
 */
class SalesOrderLine extends BaseSalesOrderLine
{
    use Traits\ProductDimensionMutators;

    protected $fillable = [
		'sales_order_id',
		'item_number',
		'article_number',
		'product_id',
		'units_requested',
        'packing_instructions',
        'product_description',
		'unit_price',
		'unit_price_gbp',
		'total',
		'total_gbp',
        'total_cubic_ft',
        'total_cubic_m',

        'units_per_case',
        'inner_qty',
        'case_weight_kg',
        'unit_weight_kg',

        'height_cm',
        'length_cm',
        'width_cm',
        'unit_width_cm',
        'unit_length_cm',
        'unit_height_cm',
        'height_m',
        'length_m',
        'width_m',
        'unit_width_m',
        'unit_length_m',
        'unit_height_m',
        'barcode',
        'sales_order_notes',

        'oa_reorder_number',
        'oa_eta',
        'oa_primary_sku',
        'name'
	];

	public function getEstimatedShippingCostGbpAttribute()
    {
	    return $this->total_cubic_ft * setting('shipping_cost_per_cubic_foot');
    }

    public function selected_media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function getTotalCubicFtAttribute()
    {
        return round($this->total_cubic_m * setting('cubic_metres_to_feet'), 2);
    }

    public function setTotalCubicFtAttribute($ft)
    {
        return $this->total_cubic_m = $ft / setting('cubic_metres_to_feet');
    }

    public function getCaseCubicMAttribute()
    {
	    return $this->length_m * $this->width_m * $this->height_m;
    }

    public function getCaseCubicFtAttribute()
    {
        return $this->case_cubic_m * setting('cubic_metres_to_feet');
    }



    /**
     * @return array|null
     */
    public function getWorstEstimatedLandedCostDataAttribute()
    {
        $consignment = $this->consignments()
            ->orderBy('est_landed_cost_per_unit_gbp', 'DESC')
            ->first();
        return optional($consignment)->pivot;
    }

    public function getLiveEstimatedLandedCostAttribute()
    {
        if ($this->product_id < 1) {
            return 0;
        }
        $calculator = new LandedCostCalculator(
            $this->sales_order->shipping_cost_per_cubic_foot
        );

        try {
            $shipping = $calculator->calculateWithDuties(
                $this->case_cubic_ft,
                $this->units_per_case,
                $this->unit_price,
                $this->product->is_excluded_from_commission
            );
        } catch (\Exception $ex) {
            throw $ex;
        }

        return round($shipping['est_landed_cost_per_unit_gbp'], 2);
    }

    public function consignments()
    {
        return parent::consignments()->withTrashed();
    }


    // ---------------------- ORDER ACKNOWLEDGEMENT STUFF --------------------

    public function invoice_lines()
    {
        return $this->invoice_lines_sales_order_line;
    }

    public function getQtyReceivedAttribute()
    {
        return InvoiceLine::query()->where('sales_order_line_id', $this->id)->whereHas('invoice', function($query) {
            return $query->whereNull('deleted_at');
        })->sum('qty');
    }

    public function getQtyRemainingAttribute()
    {
        // this is only for use when dealing with Invoices
        return $this->units_requested - $this->qty_received;
    }


}
