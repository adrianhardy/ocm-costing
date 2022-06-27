<?php

namespace App\Transformers;

use App\Models\AmazonGuidance;
use League\Fractal\TransformerAbstract;

class AmazonGuidanceTransformer extends TransformerAbstract
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
    public function transform(AmazonGuidance $guidance)
    {

        $json = [];

        if($guidance->getFirstMedia('sl')) {
            $json['sl'] = $guidance->getFirstMedia('sl')->getFullUrl();
        }
        if($guidance->getFirstMedia('local_fba')) {
            $json['local_fba'] = $guidance->getFirstMedia('local_fba')->getFullUrl();
        }
        if($guidance->getFirstMedia('enf_fba')) {
            $json['enf_fba'] = $guidance->getFirstMedia('enf_fba')->getFullUrl();
        }

        return $json;

    }
}
