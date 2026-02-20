<?php
declare(strict_types=1);

namespace App\Application\Auth\Command;

final class RefreshTokenCommand
{
    public function __construct(
        /** Raw refresh token from the httpOnly cookie */
        public readonly string $rawRefreshToken,
    ) {}
}
