<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\RevokeAllDevicesCommand;
use App\Application\Auth\Port\RefreshSessionRepositoryInterface;

/**
 * Revokes all active sessions for the authenticated user and increments
 * session_version on every record.
 *
 * Effect:
 *  - All refresh tokens are immediately revoked (cannot be used again).
 *  - Any access tokens still within their TTL will fail validation on the next
 *    request because the JWTSessionVersionListener will find a bumped session_version.
 */
final class RevokeAllDevicesHandler
{
    public function __construct(
        private readonly RefreshSessionRepositoryInterface $sessions,
    ) {
    }

    public function __invoke(RevokeAllDevicesCommand $cmd): void
    {
        $this->sessions->revokeAndIncrementVersionForUser($cmd->userId);
    }
}
