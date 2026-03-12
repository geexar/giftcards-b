<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ThirdPartyServiceException extends HttpException
{
    public function __construct(?string $message = null)
    {
        // In production, always hide the real message
        $finalMessage = App::isProduction()
            ? __("service is currently unavailable")
            : ($message ?? __("service is currently unavailable"));

        parent::__construct(503, $finalMessage);
    }
}
