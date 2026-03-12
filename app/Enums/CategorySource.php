<?php

namespace App\Enums;

enum CategorySource: string
{
    case LOCAL = 'local';
    case API = 'api';
}
