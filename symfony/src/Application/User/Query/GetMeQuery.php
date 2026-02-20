<?php
declare(strict_types=1);

namespace App\Application\User\Query;

final class GetMeQuery
{
    public function __construct(
        public readonly string $userId,
    ) {
    }
}
