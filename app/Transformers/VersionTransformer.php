<?php

namespace App\Transformers;

use App\Models\Version;
use League\Fractal\TransformerAbstract;

class VersionTransformer extends TransformerAbstract
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
        'user'
    ];

    public function includeUser(Version $version)
    {
        if (!$version->user) {
            return $this->null();
        }
        return $this->item($version->user, new UserTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Version $version)
    {
        return $version->only([
            'version_id',
            'model_data',
            'created_at',
            'updated_at'
        ]);
    }
}
