<?php
declare(strict_types=1);

namespace App\Domain\Route\ValueObject;

enum RouteStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';
}
