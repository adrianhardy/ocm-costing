<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;
use BeyondCode\Comments\Traits\HasComments;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * Class Product
 * @package App\Models
 *
 * @property $length_cm
 * @property $width_cm
 * @property $height_cm
 * @property $case_cubic_m
 *
 * @property $unit_depth_m aliases to unit_length_m
 * @property $unit_length_cm
 * @property $unit_width_cm
 * @property $unit_height_cm
 *
 * @property-read $current_landed_cost_per_unit_gbp
 */
class Product extends BaseProduct implements HasMedia
{
    use HasMediaTrait;
    use HasComments;
    use LogsActivity;

    protected static $logAttributes = [
        'item_number',
        'article_number',
        'name',
        'barcode',
        'length_m',
        'width_m',
        'height_m',
        'unit_length_m',
        'unit_width_m',
        'unit_height_m',
        'unit_length_cm',
        'unit_width_cm',
        'unit_height_cm',
        'length_cm',
        'width_cm',
        'height_cm',
        'units_per_case',
        'inner_qty',
        'packing_instructions',
        'product_description',
        'unit_price',
        'case_weight_kg',
        'unit_weight_kg'
    ];

	protected $fillable = [
		'item_number',
		'article_number',
		'name',
		'packing_instructions',
		'barcode',
        'length_m',
        'width_m',
        'height_m',
        'length_cm',
        'width_cm',
        'height_cm',
        'unit_length_m',
        'unit_width_m',
        'unit_height_m',
        'unit_length_cm',
        'unit_width_cm',
        'unit_height_cm',
        'units_per_case',
        'inner_qty',
        'packing_instructions',
        'product_description',
        'unit_price',
        'case_weight_kg',
        'unit_weight_kg'
    ];

    public function registerMediaCollections()
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->registerMediaConversions(function () {
                return $this
                    ->addMediaConversion('thumb')
                    ->nonQueued()
                    ->width(200)
                    ->height(200);
            });
    }

    public function getCaseCubicFtAttribute()
    {
        return round($this->case_cubic_m * setting('cubic_metres_to_feet'), 2);
    }

    public function getCaseCubicMAttribute()
    {
        return $this->length_m *
            $this->width_m *
            $this->height_m;
    }

    public function getLengthCmAttribute()
    {
        return round($this->length_m * 100, 2);
    }

    public function getWidthCmAttribute()
    {
        return round($this->width_m * 100, 2);
    }

    public function getHeightCmAttribute()
    {
        return round($this->height_m * 100, 2);
    }

    public function getUnitLengthCmAttribute()
    {
        return round($this->unit_length_m * 100, 2);
    }

    public function getUnitWidthCmAttribute()
    {
        return round($this->unit_width_m * 100, 2);
    }

    public function getUnitHeightCmAttribute()
    {
        return round($this->unit_height_m * 100, 2);
    }

    public function setLengthCmAttribute($cm)
    {
        $this->length_m = $cm/100;
    }

    public function setWidthCmAttribute($cm)
    {
        $this->width_m = $cm/100;
    }

    public function setHeightCmAttribute($cm)
    {
        $this->height_m = $cm/100;
    }

    public function setUnitLengthCmAttribute($cm)
    {
        $this->unit_length_m = $cm/100;
    }

    public function setUnitWidthCmAttribute($cm)
    {
        $this->unit_width_m = $cm/100;
    }

    public function setUnitHeightCmAttribute($cm)
    {
        $this->unit_height_m = $cm/100;
    }


    /**
     * @param $model
     * @return false
     */
    public function needsCommentApproval($model)
    {
        return false;
    }

    public function comments()
    {
        return $this
            ->morphMany(config('comments.comment_class'), 'commentable')
            ->orderBy('created_at', 'DESC');
    }

    public function getCurrentLandedCostPerUnitGbpAttribute()
    {

        $landedCostData = \DB::table('sales_order_lines_consignments')
            ->leftJoin('sales_order_lines', 'sales_order_lines.id', '=', 'sales_order_lines_consignments.sales_order_line_id')
            ->where('sales_order_lines.product_id', '=', $this->id)
            ->orderBy('sales_order_lines.created_at', 'DESC')
            ->limit(1)
            ->first();

        return $landedCostData->est_landed_cost_per_unit_gbp;
    }

}
