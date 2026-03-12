<?php

namespace App\Enums;

enum ProductSyncType: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
}
