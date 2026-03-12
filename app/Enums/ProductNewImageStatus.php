<?php

namespace App\Enums;

enum ProductNewImageStatus: string
{
    case PENDING = 'pending';
    case APPLIED = 'applied';
    case CANCELED = 'canceled';
}
