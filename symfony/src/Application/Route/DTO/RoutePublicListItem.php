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

        public readonly ?float $startLat,
        public readonly ?float $startLng,

        public readonly int $distanceM,
        public readonly int $elevationGainM,
        public readonly int $elevationLossM,

        public readonly bool $isActive,
        public readonly string $createdAt,
        public readonly ?string $image = null,
        public readonly ?string $creatorName = null,
        public readonly ?string $creatorSlug = null,
        public readonly ?string $creatorAvatar = null,
        public readonly ?int $durationSeconds = null,
    ) {
    }
}
