<?php

namespace App\Enums;

enum OrderStatus: string
{
    case COMPLETED = 'completed';
    case PROCESSED = 'processed';
    case PARTIALLY_COMPLETED = 'partially_completed';
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case WAITING_FOR_RESPONSE = 'waiting_for_response';
    case WAITING_FOR_ACTION = 'waiting_for_action';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';
}
