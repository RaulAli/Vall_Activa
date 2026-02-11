<?php
declare(strict_types=1);

namespace App\Application\Route\PublicQuery;

final class GetPublicRouteBySlugQuery
{
    public function __construct(
        public readonly string $slug
    ) {
    }
}
