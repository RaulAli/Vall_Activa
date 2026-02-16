<?php
declare(strict_types=1);

namespace App\Application\Route\Port;

use App\Application\Route\DTO\RoutePublicDetails;
use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Route\DTO\RouteFiltersMeta;
use App\Application\Route\DTO\RouteMapMarker;
use App\Application\Shared\DTO\PaginatedResult;

interface RoutePublicReadRepositoryInterface
{
    public function listPublic(RoutePublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult;

    /** @return RouteMapMarker[] */
    public function listMapMarkers(RoutePublicFilters $filters, int $limit = 5000): array;

    public function findPublicBySlug(string $slug): ?RoutePublicDetails;

    public function getFiltersMeta(RoutePublicFilters $filters): RouteFiltersMeta;
}
