<?php

namespace App\Enums;

enum StockStatus: string
{
    case OUT_OF_STOCK = 'out_of_stock';
    case LOW = 'low';
    case NORMAL = 'normal';
}
