<?php

namespace App\Enums;

enum TransactionType: string
{
    case TOP_UP = 'top_up';
    case MANUAL_ADJUSTMENT = 'manual_adjustment';
    case PURCHASE = 'purchase';
    case REFUND = 'refund';
}
