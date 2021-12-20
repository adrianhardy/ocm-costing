<?php

namespace App\Mail;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierSalesOrderFinalised extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var SalesOrder
     */
    private SalesOrder $salesOrder;
    /**
     * @var User
     */
    private User $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SalesOrder $salesOrder, User $user)
    {
        $this->salesOrder = $salesOrder;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Supplier has finalised a quote')
            ->markdown('emails.suppliers.supplier-finalised-sales-order-notification', [
                'sales_order' => $this->salesOrder,
                'user' => $this->user
            ]);
    }
}
