<?php

namespace App\Mail;

use App\Models\SalesOrder;
use App\Models\SalesOrderAuthentication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierSalesOrderInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var SalesOrderAuthentication
     */
    private SalesOrderAuthentication $authentication;

    /**
     * SupplierSalesOrderInvitation constructor.
     * @param SalesOrderAuthentication $authentication
     */
    public function __construct(
        SalesOrderAuthentication $authentication
    ) {
        $this->authentication = $authentication;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('emails.supplier.invitation', [
                'token' => $this->authentication
            ])
            ->subject('Invitation to collaborate on a sales order');
    }
}
