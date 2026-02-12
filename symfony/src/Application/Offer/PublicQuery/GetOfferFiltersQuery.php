<?php
declare(strict_types=1);

namespace App\Application\Offer\PublicQuery;

use App\Application\Offer\DTO\OfferPublicFilters;

final class GetOfferFiltersQuery
{
    public function __construct(
        public readonly OfferPublicFilters $filters
    ) {
    }
}
