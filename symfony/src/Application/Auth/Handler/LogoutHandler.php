<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\LogoutCommand;
use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use App\Application\Auth\Port\TokenBlacklistRepositoryInterface;

final class LogoutHandler
{
    public function __construct(
        private readonly RefreshSessionRepositoryInterface $sessions,
        private readonly TokenBlacklistRepositoryInterface $blacklist,
    ) {}

    public function __invoke(LogoutCommand $cmd): void
    {
        $tokenHash = hash('sha256', $cmd->rawRefreshToken);

        // Look up the session to get its expiry (needed for blacklist TTL)
        $session = $this->sessions->findActiveByTokenHash($tokenHash);

        if ($session !== null) {
            // Blacklist the token so it can't be reused even before DB commit
            $this->blacklist->add($tokenHash, $cmd->userId, $session['expiresAt']);
            // Revoke the specific session by device
            $this->sessions->revoke($session['id']);
        }
        // If session already gone / token already used — silently succeed
        // (idempotent logout: already logged out is still "logged out")
    }
}
