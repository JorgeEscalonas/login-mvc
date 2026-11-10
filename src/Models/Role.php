<?php

declare(strict_types=1);

namespace App\Models;

enum Role: string
{
    case Admin = 'admin';
    case User = 'user';
}