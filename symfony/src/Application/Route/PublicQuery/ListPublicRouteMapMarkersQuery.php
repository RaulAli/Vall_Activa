<?php
declare(strict_types=1);

namespace App\Application\Route\PublicQuery;

use App\Application\Route\DTO\RoutePublicFilters;

final class ListPublicRouteMapMarkersQuery
{
    public function __construct(
        public readonly RoutePublicFilters $filters,
        public readonly int $limit = 5000
    ) {
    }
}
