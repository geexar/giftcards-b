<?php

namespace App\Enums;

enum OrderStatus: string
{
    case WAITING_PAYMENT = 'waiting_payment';
    case EXPIRED = 'expired';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case PROCESSED = 'processed';
    case PARTIALLY_COMPLETED = 'partially_completed';
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case AWAITING_ACTION = 'awaiting_action';
    case FAILED = 'failed';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';
}
