<?php
declare(strict_types=1);

namespace App\Application\Offer\DTO;

final class OfferPublicListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $businessId,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $price,
        public readonly string $currency,
        public readonly ?string $image,
        public readonly string $discountType,
        public readonly string $status,
        public readonly int $quantity,
        public readonly int $pointsCost,
        public readonly bool $isActive,
        public readonly string $createdAt,
    ) {
    }
}
