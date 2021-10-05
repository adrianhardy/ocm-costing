<?php

namespace App\Events;

use App\Models\InspectionReport;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductInspectionApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var InspectionReport
     */
    private $inspectionReport;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(InspectionReport $inspectionReport)
    {
        $this->inspectionReport = $inspectionReport;
    }
}
