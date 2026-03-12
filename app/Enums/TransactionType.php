<?php

namespace App\Enums;

enum TransactionType: string
{
    case TOPUP = 'top_up';
    case MANUAL_ADJUSTMENT = 'manual_adjustment';
    case PURCHASE = 'purchase';
    case REFUND = 'refund';
}
