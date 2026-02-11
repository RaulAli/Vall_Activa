<?php
declare(strict_types=1);

namespace App\Application\Route\Handler;

use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Route\PublicQuery\ListPublicRoutesQuery;

final class ListPublicRoutesHandler
{
    public function __construct(private readonly RoutePublicReadRepositoryInterface $repo)
    {
    }

    public function __invoke(ListPublicRoutesQuery $query)
    {
        $page = max(1, $query->page);
        $limit = min(100, max(1, $query->limit));

        $sort = strtolower($query->sort);
        $sort = in_array($sort, ['recent', 'distance', 'gain'], true) ? $sort : 'recent';

        $order = strtolower($query->order);
        $order = in_array($order, ['asc', 'desc'], true) ? $order : 'desc';

        return $this->repo->listPublic(
            filters: $query->filters,
            page: $page,
            limit: $limit,
            sort: $sort,
            order: $order
        );
    }
}
