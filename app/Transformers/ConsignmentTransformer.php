<?php

namespace App\Transformers;

use App\Models\Consignment;
use League\Fractal\TransformerAbstract;
use Spatie\MediaLibrary\Models\Media;

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
        'pivot', 'sales_order', 'sales_order_lines', 'media'
    ];

    public function includeMedia(Consignment $consignment){
        return $this->collection($consignment->getMedia('default'), function (Media $media) {
            return [
                'id' => $media->id,
                'url' => $media->getFullUrl(),
                'file_name' => $media->file_name,
                'type' => $media->getTypeFromExtension()
            ];
        });
    }

    public function includeSalesOrder(Consignment $consignment){
        if(!$consignment->sales_order) {
            return $this->null();
        }
        return $this->item($consignment->sales_order, new SalesOrderTransformer);
    }

    public function includeSalesOrderLines(Consignment $consignment){
        if(!$consignment->sales_order || !$consignment->sales_order_lines) {
            return $this->null();
        }
        return $this->collection($consignment->sales_order_lines, new SalesOrderLineTransformer());
    }

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
            'shipping_agent_ref',
            'est_cost_cuft_gbp',
            'departed_at',
            'docked_at',
            'estimated_arrival_at',
            'estimated_departure_at'
        ]);
    }
}
