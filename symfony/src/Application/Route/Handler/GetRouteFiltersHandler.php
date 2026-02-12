<?php
declare(strict_types=1);

namespace App\Application\Route\Handler;

use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Route\PublicQuery\GetRouteFiltersQuery;

final class GetRouteFiltersHandler
{
    public function __construct(private readonly RoutePublicReadRepositoryInterface $repo)
    {
    }

    public function __invoke(GetRouteFiltersQuery $query)
    {
        return $this->repo->getFiltersMeta($query->filters);
    }
}
