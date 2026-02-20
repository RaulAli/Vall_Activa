<?php
declare(strict_types=1);

namespace App\Application\Auth\Port;

interface JwtTokenGeneratorInterface
{
    /**
     * Issues a signed, short-lived JWT access token.
     *
     * @param array<string, mixed> $extraClaims Additional JWT claims (e.g. email, roles)
     */
    public function generate(string $userId, string $email, array $extraClaims = []): string;
}
