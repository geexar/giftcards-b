<?php

namespace App\Enums;

enum ProductSource: string
{
    case LOCAL = 'local';
    case API = 'api';
}
