<?php
declare(strict_types=1);

namespace App\Application\Offer\PublicQuery;

use App\Application\Offer\DTO\OfferPublicFilters;

final class ListPublicOffersQuery
{
    public function __construct(
        public readonly OfferPublicFilters $filters,
        public readonly int $page = 1,
        public readonly int $limit = 20,
        public readonly string $sort = 'recent', // recent|price|points
        public readonly string $order = 'desc'   // asc|desc
    ) {
    }
}
