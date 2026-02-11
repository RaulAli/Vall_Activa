<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class RoutePublicListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $sportId,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $polyline,
        public readonly ?float $minLat,
        public readonly ?float $minLng,
        public readonly ?float $maxLat,
        public readonly ?float $maxLng,
        public readonly int $distanceM,
        public readonly int $elevationGainM,
        public readonly int $elevationLossM,
        public readonly bool $isActive,   // ✅ ESTE CAMPO ES EL QUE TE FALTA
        public readonly string $createdAt
    ) {
    }
}
