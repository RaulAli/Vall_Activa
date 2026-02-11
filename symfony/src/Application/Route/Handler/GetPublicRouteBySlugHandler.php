<?php
declare(strict_types=1);

namespace App\Application\Route\Handler;

use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Route\PublicQuery\GetPublicRouteBySlugQuery;

final class GetPublicRouteBySlugHandler
{
    public function __construct(
        private readonly RoutePublicReadRepositoryInterface $repo
    ) {
    }

    public function __invoke(GetPublicRouteBySlugQuery $query)
    {
        return $this->repo->findPublicBySlug($query->slug);
    }
}
