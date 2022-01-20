<?php

namespace App\Transformers;

use App\Models\Consignment;
use League\Fractal\TransformerAbstract;

class ConsignmentTransformer extends TransformerAbstract
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
        'pivot'
    ];

    public function includePivot(Consignment $consignment)
    {
        if (!$consignment->pivot) {
            return $this->null();
        }
        return $this->primitive($consignment->pivot);
    }
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Consignment $consignment)
    {
        return $consignment->only([
            'id',
            'created_at',
            'updated_at',
            'invoice_amt',
            'reference',
            'supplier_ref',
            'our_order_number',
            'vessel_name',
            'container_number',
            'bl_number',
            'shipping_agent_ref'
        ]);
    }
}
