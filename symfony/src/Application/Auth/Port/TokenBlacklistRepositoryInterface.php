<?php
declare(strict_types=1);

namespace App\Application\Auth\Port;

interface TokenBlacklistRepositoryInterface
{
    /**
     * Adds a token hash to the blacklist with its expiry.
     */
    public function add(string $tokenHash, string $userId, \DateTimeImmutable $expiresAt): void;

    /**
     * Checks whether a token hash is on the blacklist (and not yet expired).
     */
    public function isBlacklisted(string $tokenHash): bool;
}
