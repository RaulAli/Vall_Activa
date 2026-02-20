<?php
declare(strict_types=1);

namespace App\Application\Auth\DTO;

final class UserAuthData
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $hashedPassword,
        public readonly bool $isActive,
    ) {
    }
}
