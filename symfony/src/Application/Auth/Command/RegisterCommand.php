<?php
declare(strict_types=1);

namespace App\Application\Auth\Command;

final class RegisterCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $plainPassword,
        public readonly string $role,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $bio = null,
        public readonly ?string $city = null,
        /** @var list<string>|null */
        public readonly ?array $sports = null,
    ) {
    }
}
