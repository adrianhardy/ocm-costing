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
 */
class Product extends BaseProduct implements HasMedia
{
    use HasMediaTrait;
    use HasComments;
    use LogsActivity;

    static $logFillable = true;

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
        'units_per_case',
        'inner_qty',
        'packing_instructions',
        'product_description',
        'unit_price',
        'case_weight_kg'
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


    public function getLengthCmAttribute()
    {
        return round($this->length_m / 100, 2);
    }

    public function getWidthCmAttribute()
    {
        return round($this->width_m / 100, 2);
    }

    public function getHeightCmAttribute()
    {
        return round($this->height_m / 100, 2);
    }

    public function setLengthCmAttribute($cm)
    {
        $this->length_m = $cm*100;
    }

    public function setWidthCmAttribute($cm)
    {
        $this->width_m = $cm*100;
    }

    public function setHeightCmAttribute($cm)
    {
        $this->height_m = $cm*100;
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

}
