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
        'lines', 'supplier', 'currency'
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
            'reference',
            'created_at',
            'updated_at',
            'sent_at', 'fx_rate',
            'total', 'total_gbp',
            'shipping_cost',
            'shipping_cost_gbp',
            'goods_cost', 'goods_cost_gbp',
        ]);
    }
}
