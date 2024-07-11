<?php

namespace App\Transformers;

use App\Models\Invoice;
use League\Fractal\TransformerAbstract;

class InvoiceTransformer extends TransformerAbstract
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
        'sales_order', 'creator', 'approver', 'lines'
    ];

    public function includeSalesOrder(Invoice $invoice)
    {
        if (!$invoice->sales_order) {
            return $this->null();
        }
        return $this->item($invoice->sales_order, new SalesOrderTransformer());
    }

    public function includeCreator(Invoice $invoice){
        return $this->item($invoice->creator, new UserTransformer());
    }

    public function includeApprover(Invoice $invoice) {
        if($invoice->approver) {
            return $this->item($invoice->approver, new UserTransformer());
        }
        return $this->null();
    }

    public function includeLines(Invoice $invoice){
        return $this->collection($invoice->invoice_lines, new InvoiceLineTransformer());
    }

    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Invoice $invoice)
    {
        return $invoice->only([
            'id', 'created_at', 'updated_at', 'deleted_at','reference', 'approved_at', 'sub_total', 'is_approval_required',
            'invoice_date'
        ]);
    }
}
