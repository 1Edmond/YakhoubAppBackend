<?php

namespace App\Listeners;

use App\Events\ReductionRequestedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyVendorOfReductionRequest implements ShouldQueue
{
    public function handle(ReductionRequestedEvent $event)
    {
        // Notification logic here (e.g., FCM)
    }
}
