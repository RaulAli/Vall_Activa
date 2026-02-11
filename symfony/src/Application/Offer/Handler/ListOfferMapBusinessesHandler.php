<?php
declare(strict_types=1);

namespace App\Application\Offer\Handler;

use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Offer\PublicQuery\ListOfferMapBusinessesQuery;

final class ListOfferMapBusinessesHandler
{
    public function __construct(private readonly OfferPublicReadRepositoryInterface $repo)
    {
    }

    public function __invoke(ListOfferMapBusinessesQuery $query): array
    {
        return $this->repo->listMapBusinesses($query->filters);
    }
}
