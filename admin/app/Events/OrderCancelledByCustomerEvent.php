<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * CDC 3.7 : notification "Annulation" -> propriétaire du magasin.
 * Brancher un listener qui utilise le mécanisme de push existant (cf. Helpers::send_push_notif_to_device).
 */
class OrderCancelledByCustomerEvent
{
    use Dispatchable;

    public function __construct(public readonly Order $order)
    {
    }
}
