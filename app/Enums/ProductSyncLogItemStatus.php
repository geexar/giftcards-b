<?php

namespace App\Enums;

enum ProductSyncLogItemStatus: string
{
    case ADDED = 'added';
    case REMOVED = 'removed';
    case UPDATED = 'updated';
}
