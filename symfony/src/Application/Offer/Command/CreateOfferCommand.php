<?php
declare(strict_types=1);

namespace App\Application\Offer\Command;

use App\Application\Shared\Security\Actor;

final class CreateOfferCommand
{
    public function __construct(
        public readonly Actor $actor,

        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,

        public readonly string $price,
        public readonly string $currency,

        public readonly bool $isActive,
        public readonly int $quantity,
        public readonly int $pointsCost,

        public readonly ?string $image,

        public readonly string $discountType, // AMOUNT|PERCENT|NONE
        public readonly string $status        // DRAFT|PUBLISHED|ARCHIVED
    ) {}
}
