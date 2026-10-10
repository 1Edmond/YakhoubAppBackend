<?php

namespace App\Exceptions;

class ReductionException extends \RuntimeException
{
    public function __construct(string $message, public readonly int $httpStatus = 422)
    {
        parent::__construct($message);
    }
}
