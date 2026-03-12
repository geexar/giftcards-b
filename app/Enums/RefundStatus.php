<?php

namespace App\Enums;

enum RefundStatus: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
}
