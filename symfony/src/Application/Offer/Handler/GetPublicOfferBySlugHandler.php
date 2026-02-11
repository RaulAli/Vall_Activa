<?php
declare(strict_types=1);

namespace App\Application\Offer\Handler;

use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Offer\PublicQuery\GetPublicOfferBySlugQuery;

final class GetPublicOfferBySlugHandler
{
    public function __construct(
        private readonly OfferPublicReadRepositoryInterface $repo
    ) {
    }

    public function __invoke(GetPublicOfferBySlugQuery $query)
    {
        return $this->repo->findPublicBySlug($query->slug);
    }
}
