<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class RouteMapMarker
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly ?float $lat,
        public readonly ?float $lng
    ) {
    }
}
