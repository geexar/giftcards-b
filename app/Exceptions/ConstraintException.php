<?php

namespace App\Exceptions;

use Exception;

class ConstraintException extends Exception
{
    public function __construct(string $message, int $code = 409)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return error($this->message, $this->code);
    }
}
