<?php
declare(strict_types=1);

namespace App\Application\Auth\Command;

final class RegisterCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $plainPassword,
        public readonly string $slug,
    ) {}
}
