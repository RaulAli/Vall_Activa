<?php
declare(strict_types=1);

namespace App\Application\Offer\DTO;

final class BusinessMapMarker
{
    public function __construct(
        public readonly string $businessUserId,
        public readonly string $slug,
        public readonly string $name,
        public readonly float $lat,
        public readonly float $lng,
        public readonly ?string $profileIcon,
        public readonly int $offersCount
    ) {
    }
}
