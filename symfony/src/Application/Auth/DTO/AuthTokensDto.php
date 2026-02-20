<?php
declare(strict_types=1);

namespace App\Application\Auth\DTO;

final class AuthTokensDto
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $rawRefreshToken,
        /** Seconds until refresh token expires */
        public readonly int $refreshTtl,
        public readonly string $userId,
        public readonly string $email,
    ) {
    }
}
