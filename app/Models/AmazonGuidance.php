<?php

namespace App\Models;

use App\Models\Base\AmazonGuidance as BaseAmazonGuidance;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class AmazonGuidance extends BaseAmazonGuidance implements HasMedia
{
    use HasMediaTrait;

    public function registerMediaCollections()
    {
        $this->addMediaCollection('sl')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->singleFile();

        $this->addMediaCollection('local_fba')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->singleFile();

        $this->addMediaCollection('efn_fba')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->singleFile();

    }
}
