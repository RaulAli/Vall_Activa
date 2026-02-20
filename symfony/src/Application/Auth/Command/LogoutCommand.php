<?php
declare(strict_types=1);

namespace App\Application\Auth\Command;

final class LogoutCommand
{
    public function __construct(
        /** Raw refresh token from httpOnly cookie */
        public readonly string $rawRefreshToken,
        public readonly string $userId,
    ) {
    }
}
