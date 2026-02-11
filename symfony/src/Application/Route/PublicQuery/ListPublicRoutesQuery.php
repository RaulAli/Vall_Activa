<?php
declare(strict_types=1);

namespace App\Application\Route\PublicQuery;

use App\Application\Route\DTO\RoutePublicFilters;

final class ListPublicRoutesQuery
{
    public function __construct(
        public readonly RoutePublicFilters $filters,
        public readonly int $page = 1,
        public readonly int $limit = 20,
        public readonly string $sort = 'recent', // recent|distance|gain
        public readonly string $order = 'desc'   // asc|desc
    ) {
    }
}
