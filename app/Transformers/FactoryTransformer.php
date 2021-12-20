<?php

namespace App\Transformers;

use App\Models\Factory;
use League\Fractal\TransformerAbstract;

class FactoryTransformer extends TransformerAbstract
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
    public function transform(Factory $factory)
    {
        return $factory->only([
            'id',
            'name',
            'country',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone',
            'shipping_name',
            'products_description'
        ]);
    }
}
