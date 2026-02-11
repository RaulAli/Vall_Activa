<?php
declare(strict_types=1);

namespace App\Application\Offer\DTO;

final class OfferPublicFilters
{
    public function __construct(
        public readonly ?string $q = null,
        public readonly ?string $discountType = null,
        public readonly ?string $priceMin = null,
        public readonly ?string $priceMax = null,
        public readonly ?int $pointsMin = null,
        public readonly ?int $pointsMax = null,
        public readonly ?bool $inStock = null,

        // bbox: minLng,minLat,maxLng,maxLat (viewport del mapa)
        public readonly ?float $bboxMinLng = null,
        public readonly ?float $bboxMinLat = null,
        public readonly ?float $bboxMaxLng = null,
        public readonly ?float $bboxMaxLat = null,
    ) {
    }
}
