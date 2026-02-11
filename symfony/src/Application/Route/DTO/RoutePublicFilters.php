<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class RoutePublicFilters
{
    public function __construct(
        public readonly ?string $sportCode = null,
        public readonly ?int $distanceMin = null,
        public readonly ?int $distanceMax = null,
        public readonly ?int $gainMin = null,
        public readonly ?int $gainMax = null,

        // bbox en orden: minLng, minLat, maxLng, maxLat
        public readonly ?float $bboxMinLng = null,
        public readonly ?float $bboxMinLat = null,
        public readonly ?float $bboxMaxLng = null,
        public readonly ?float $bboxMaxLat = null,

        public readonly ?string $q = null
    ) {
    }
}
