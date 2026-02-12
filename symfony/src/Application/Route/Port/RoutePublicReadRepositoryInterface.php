<?php
declare(strict_types=1);

namespace App\Application\Route\Port;

use App\Application\Route\DTO\RoutePublicDetails;
use App\Application\Route\DTO\RoutePublicFilters;
use App\Application\Shared\DTO\PaginatedResult;
use App\Application\Route\DTO\RouteFiltersMeta;


interface RoutePublicReadRepositoryInterface
{
    public function listPublic(RoutePublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult;

    public function findPublicBySlug(string $slug): ?RoutePublicDetails;

    public function getFiltersMeta(RoutePublicFilters $filters): RouteFiltersMeta;

}
