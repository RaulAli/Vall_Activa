<?php
declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\Auth\Port\PasswordHasherInterface;
use App\Application\User\Command\ChangePasswordCommand;
use App\Application\User\Port\UserReadRepositoryInterface;
use App\Application\User\Port\UserWriteRepositoryInterface;

final class ChangePasswordHandler
{
    public function __construct(
        private readonly UserReadRepositoryInterface $readUsers,
        private readonly UserWriteRepositoryInterface $writeUsers,
        private readonly PasswordHasherInterface $hasher,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(ChangePasswordCommand $cmd): void
    {
        $hashedPassword = $this->readUsers->findHashedPassword($cmd->userId);

        if ($hashedPassword === null) {
            throw new \DomainException('user_not_found');
        }

        if (!$this->hasher->verify($hashedPassword, $cmd->currentPassword)) {
            throw new \DomainException('invalid_current_password');
        }

        $newHashed = $this->hasher->hash($cmd->newPassword);

        $this->writeUsers->changePassword($cmd->userId, $newHashed);
    }
}
