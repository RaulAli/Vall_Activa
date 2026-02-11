<?php
declare(strict_types=1);

namespace App\Application\Offer\PublicQuery;

final class GetPublicOfferBySlugQuery
{
    public function __construct(
        public readonly string $slug
    ) {
    }
}
