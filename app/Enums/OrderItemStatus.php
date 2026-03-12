<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case PROCESSING = 'processing';
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case COMPLETED = 'completed';
    case PARTIALLY_FULFILLED = 'partially_fulfilled';
    case REJECTED = 'rejected';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
