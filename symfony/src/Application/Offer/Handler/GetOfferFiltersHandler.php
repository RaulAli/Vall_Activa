<?php
declare(strict_types=1);

namespace App\Application\Offer\Handler;

use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Offer\PublicQuery\GetOfferFiltersQuery;

final class GetOfferFiltersHandler
{
    public function __construct(
        private readonly OfferPublicReadRepositoryInterface $repo
    ) {
    }

    public function __invoke(GetOfferFiltersQuery $query)
    {
        return $this->repo->getFiltersMeta($query->filters);
    }
}
