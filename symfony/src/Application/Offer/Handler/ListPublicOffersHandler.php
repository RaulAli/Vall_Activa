<?php
declare(strict_types=1);

namespace App\Application\Offer\Handler;

use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Offer\PublicQuery\ListPublicOffersQuery;

final class ListPublicOffersHandler
{
    public function __construct(private readonly OfferPublicReadRepositoryInterface $repo)
    {
    }

    public function __invoke(ListPublicOffersQuery $query)
    {
        $page = max(1, $query->page);
        $limit = min(100, max(1, $query->limit));

        return $this->repo->listPublic($page, $limit);
    }
}
