<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class RoutePublicDetails
{
    public function __construct(
        public readonly string $id,
        public readonly string $sportId,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,

        public readonly string $visibility,
        public readonly string $status,

        public readonly ?float $startLat,
        public readonly ?float $startLng,
        public readonly ?float $endLat,
        public readonly ?float $endLng,

        public readonly ?float $minLat,
        public readonly ?float $minLng,
        public readonly ?float $maxLat,
        public readonly ?float $maxLng,

        public readonly int $distanceM,
        public readonly int $elevationGainM,
        public readonly int $elevationLossM,

        public readonly ?string $polyline,

        public readonly string $createdAt
    ) {
    }
}
