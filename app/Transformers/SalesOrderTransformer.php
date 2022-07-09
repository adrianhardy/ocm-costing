<?php

namespace App\Transformers;

use App\Models\SalesOrder;
use League\Fractal\TransformerAbstract;

class SalesOrderTransformer extends TransformerAbstract
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
        'lines', 'supplier', 'currency', 'factory'
    ];


    /**
     * @param SalesOrder $so
     * @return \League\Fractal\Resource\Item
     */
    public function includeSupplier(SalesOrder $so)
    {
        return $this->item($so->supplier, new SupplierTransformer);
    }

    /**
     * @param SalesOrder $so
     * @return \League\Fractal\Resource\Item
     */
    public function includeCurrency(SalesOrder $so)
    {
        return $this->item($so->currency, new CurrencyTransformer());
    }


    /**
     * @param SalesOrder $so
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeFactory(SalesOrder $so)
    {
        if (!$so->factory) {
            return $this->null();
        }
        return $this->item($so->factory, new FactoryTransformer());
    }


    /**
     * @param SalesOrder $so
     * @return \League\Fractal\Resource\Collection
     */
    public function includeLines(SalesOrder $so)
    {
        return $this->collection($so->lines, new SalesOrderLineTransformer);
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(SalesOrder $so)
    {
        return $so->only([
            'id',
            'sales_order_number',
            'reference',
            'created_at',
            'updated_at',
            'deleted_at',
            'is_sent',
            'is_finalised',
            'is_supplier_finalised',
            'sent_at',
            'finalised_at',
            'supplier_finalised_at',
            'fx_rate',
            'total',
            'total_gbp',
            'shipping_cost',
            'shipping_cost_gbp',
            'goods_cost',
            'goods_cost_gbp',
            'status',
            'status_description',
            'sales_order_notes',
            'shipping_cost_per_cubic_foot',
            'supplier_reference',
            'sales_order_fx_rate'
        ]);
    }
}
