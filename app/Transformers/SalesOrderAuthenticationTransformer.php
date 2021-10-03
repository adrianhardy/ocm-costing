<?php

namespace App\Transformers;

use App\Models\SalesOrder;
use App\Models\SalesOrderAuthentication;
use League\Fractal\TransformerAbstract;

class SalesOrderAuthenticationTransformer extends TransformerAbstract
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

    public function includeUser(SalesOrderAuthentication $authentication)
    {
        return $this->item($authentication->user, new UserTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(SalesOrderAuthentication $token)
    {
        return $token->only([
            'id',
            'expires_at',
            'consumed_at'
        ]);
    }
}
