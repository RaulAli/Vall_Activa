<?php
declare(strict_types=1);

namespace App\Application\Route\Handler;

use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Route\PublicQuery\ListPublicRouteMapMarkersQuery;

final class ListPublicRouteMapMarkersHandler
{
    public function __construct(private readonly RoutePublicReadRepositoryInterface $repo)
    {
    }

    /** @return array */
    public function __invoke(ListPublicRouteMapMarkersQuery $q): array
    {
        return [
            'items' => $this->repo->listMapMarkers($q->filters, $q->limit),
        ];
    }
}
