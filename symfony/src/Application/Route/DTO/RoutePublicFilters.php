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

        // focus en orden: lng, lat, radiusMeters (PRIORIDAD SOBRE bbox)
        public readonly ?float $focusLng = null,
        public readonly ?float $focusLat = null,
        public readonly ?int $focusRadiusM = null,

        public readonly ?string $q = null
    ) {
    }

    public function hasFocus(): bool
    {
        return $this->focusLat !== null && $this->focusLng !== null && $this->focusRadiusM !== null;
    }

    public function hasBbox(): bool
    {
        return $this->bboxMinLat !== null && $this->bboxMinLng !== null
            && $this->bboxMaxLat !== null && $this->bboxMaxLng !== null;
    }
}
