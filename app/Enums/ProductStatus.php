<?php

namespace App\Enums;

enum ProductStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case DRAFTED = 'drafted';
}
