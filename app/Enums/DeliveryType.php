<?php

namespace App\Enums;

enum DeliveryType: string
{
    case INSTANT = 'instant';
    case REQUIRES_CONFIRMATION = 'requires_confirmation';
}
