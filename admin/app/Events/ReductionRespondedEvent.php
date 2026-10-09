<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\PriceReductionRequest;

class ReductionRespondedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $status;

    public function __construct(PriceReductionRequest $request, $status)
    {
        $this->request = $request;
        $this->status = $status;
    }
}
