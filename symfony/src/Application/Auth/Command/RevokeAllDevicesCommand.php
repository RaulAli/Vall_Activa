<?php
declare(strict_types=1);

namespace App\Application\Auth\Command;

final class RevokeAllDevicesCommand
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
