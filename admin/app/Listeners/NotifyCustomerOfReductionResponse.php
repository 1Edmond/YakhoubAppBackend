<?php

namespace App\Listeners;

use App\Events\ReductionRespondedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerOfReductionResponse implements ShouldQueue
{
    public function handle(ReductionRespondedEvent $event)
    {
        // Notification logic here (e.g., FCM)
    }
}
