<?php

namespace App\Exceptions;

class PaymentGatewayNotConfiguredException extends \RuntimeException
{
    public function __construct(public readonly string $gateway)
    {
        parent::__construct("Payment gateway [{$gateway}] is not configured.");
    }
}
