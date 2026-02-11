<?php
declare(strict_types=1);

namespace App\Domain\Route\ValueObject;

enum RouteVisibility: string
{
    case PUBLIC = 'PUBLIC';
    case UNLISTED = 'UNLISTED';
    case PRIVATE = 'PRIVATE';
}
