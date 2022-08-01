<?php

namespace App\Mail;

use App\Models\Consignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewInboundShipmentReadyNotification extends Mailable
{
    use Queueable, SerializesModels;

    private Consignment $consignment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Consignment $consignment)
    {
        $this->consignment = $consignment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('views.emails.new-inbound-shipment-alert');
    }
}
