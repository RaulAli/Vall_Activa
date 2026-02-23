<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class ParsedRouteData
{
    public function __construct(
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
        public readonly ?int $durationSeconds = null,
    ) {
    }
}
