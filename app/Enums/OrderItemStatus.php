<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case COMPLETED = 'completed';
    case PARTIALLY_FULFILLED = 'partially_fulfilled';
    case WAITING_RESPONSE = 'waiting_response';
    case REQUIRES_CONFIRMATION = 'requires_confirmation';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';
}
