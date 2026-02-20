<?php
declare(strict_types=1);

namespace App\Application\User\Command;

final class ChangePasswordCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {
    }
}
