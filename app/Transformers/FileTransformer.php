<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\MediaLibrary\Models\Media;

class FileTransformer extends TransformerAbstract
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
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Media $media)
    {
        // because these are private, we need a custom url
        return [
            'created_at' => $media->created_at,
            'id' => $media->id,
            'name' => $media->name,
            'url' => route('media.show', $media)
        ];
    }
}
