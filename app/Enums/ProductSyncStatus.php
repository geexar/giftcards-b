<?php

namespace App\Enums;

enum ProductSyncStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
