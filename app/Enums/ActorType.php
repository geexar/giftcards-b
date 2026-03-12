<?php

namespace App\Enums;

enum ActorType: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ADMIN = 'admin';
}
