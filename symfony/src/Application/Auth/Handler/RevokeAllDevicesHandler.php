<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\RevokeAllDevicesCommand;
use App\Application\Auth\Port\RefreshSessionRepositoryInterface;

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
