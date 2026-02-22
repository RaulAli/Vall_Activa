<?php
declare(strict_types=1);

namespace App\Application\Profile\Query;

final class GetPublicProfileQuery
{
    public function __construct(
        public readonly string  $slug,
        public readonly ?string $requestingUserId = null,
    ) {
    }
}
