<?php
declare(strict_types=1);

namespace App\Domain\Identity\ValueObject;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case BUSINESS = 'ROLE_BUSINESS';
    case GUIDE = 'ROLE_GUIDE';
    case ATHLETE = 'ROLE_ATHLETE';
}
