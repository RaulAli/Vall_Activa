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
    ) {
    }

    public function __invoke(LogoutCommand $cmd): void
    {
        $tokenHash = hash('sha256', $cmd->rawRefreshToken);

        $session = $this->sessions->findActiveByTokenHash($tokenHash);

        if ($session !== null) {
            $this->blacklist->add($tokenHash, $session['userId'], $session['expiresAt']);
            $this->sessions->revoke($session['id']);
        }
    }
}
