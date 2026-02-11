<?php
declare(strict_types=1);

namespace App\Domain\Offer\ValueObject;

enum DiscountType: string
{
    case AMOUNT = 'AMOUNT';
    case PERCENT = 'PERCENT';
    case NONE = 'NONE';
}
