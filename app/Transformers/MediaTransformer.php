<?php

namespace App\Transformers;

use App\Models\AmazonGuidance;
use App\Models\Consignment;
use App\Models\Country;
use App\Models\Factory;
use App\Models\Supplier;
use League\Fractal\TransformerAbstract;
use Spatie\MediaLibrary\Models\Media;

class MediaTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'model'
    ];


    public function includeModel(Media $media)
    {
        if (!$media->model) {
            return $this->null();
        }
        if ($media->model instanceof AmazonGuidance) {
            return $this->primitive('is amazon guidance');
        } elseif ($media->model instanceof Consignment) {
            return $this->item($media->model, new ConsignmentTransformer());
        } elseif ($media->model instanceof Factory) {
            return $this->item($media->model, new FactoryTransformer());
        } elseif ($media->model instanceof Supplier) {
            return $this->item($media->model, new SupplierTransformer());
        }
    }


    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Media $media)
    {
        $json = $media->only([
            'created_at',
            'name',
            'model_type'
        ]);

        if ($media->model instanceof Factory || $media->model instanceof Supplier) {
            $json['url'] = route('media.show', $media);
        } else {
            $json['url'] = $media->getUrl();

        }
        return $json;

    }
}
