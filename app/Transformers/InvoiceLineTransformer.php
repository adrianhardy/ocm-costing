<?php

namespace App\Transformers;

use App\Models\InvoiceLine;
use League\Fractal\TransformerAbstract;

class InvoiceLineTransformer extends TransformerAbstract
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
        'invoice', 'sales_order_line'
    ];

    public function includeInvoice(InvoiceLine $invoiceLine) {
        return $this->item($invoiceLine->invoice, new InvoiceTransformer());
    }

    public function includeSalesOrderLine(InvoiceLine $invoiceLine) {
        return $this->item($invoiceLine->sales_order_line, new SalesOrderLineTransformer());
    }
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(InvoiceLine $invoiceLine)
    {
        return $invoiceLine->only([
            'id',
            'unit_price',
            'qty',
            'qty_received_excl_me',
            'sub_total'
        ]);
    }
}
