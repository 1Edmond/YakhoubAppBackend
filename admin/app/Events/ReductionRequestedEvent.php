<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\PriceReductionRequest;
use App\Models\Order;

class ReductionRequestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $order;
    public $customer;

    public function __construct(PriceReductionRequest $request, ?Order $order = null, $customer = null)
    {
        $this->request = $request;
        $this->order = $order;
        $this->customer = $customer;
    }
}
