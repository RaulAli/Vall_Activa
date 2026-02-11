<?php
declare(strict_types=1);

namespace App\Domain\Offer\ValueObject;

enum OfferStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';
}
