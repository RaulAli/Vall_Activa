<?php
declare(strict_types=1);

namespace App\Application\Offer\PublicQuery;

final class ListPublicOffersQuery
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $limit = 20
    ) {
    }
}
