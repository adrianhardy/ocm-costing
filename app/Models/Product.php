<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;
use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Database\Eloquent\Collection;
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
 * @property Collection|ProductLandedCost[] $landed_costs

 * @property-read $current_landed_cost_per_unit_gbp
 * @property-read $case_cubic_ft
 */
class Product extends BaseProduct implements HasMedia
{
    use HasMediaTrait;
    use HasComments;
    use LogsActivity;
    use Traits\ProductDimensionMutators;

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
        'unit_weight_kg',
        'est_landed_cost_gbp',
        'is_excluded_from_commission'
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
        'unit_weight_kg',
        'est_landed_cost_gbp',
        'is_excluded_from_commission'
    ];

    public function registerMediaCollections()
    {
        $this->addMediaCollection('default')
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

    public function getUnitCubicFtAttribute()
    {
        return round($this->unit_cubic_m * setting('cubic_metres_to_feet'), 2);
    }

    public function getCaseCubicMAttribute()
    {
        return $this->length_m *
            $this->width_m *
            $this->height_m;
    }

    public function getUnitCubicMAttribute()
    {
        return $this->unit_length_m *
            $this->unit_width_m *
            $this->unit_height_m;
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

        $landedCostData = \DB::table('product_landed_costs')
            ->where('product_id', '=', $this->id)
            ->orderBy('product_landed_costs.date', 'DESC')
            ->limit(1)
            ->first();

        if(!$landedCostData){
            return null;
        }

        return $landedCostData->landed_cost_per_unit_gbp;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function landed_costs()
    {
        return $this->hasMany(ProductLandedCost::class);
    }

}
