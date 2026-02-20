<?php
declare(strict_types=1);

namespace App\Application\Auth\Port;

use App\Application\Auth\DTO\UserAuthData;

interface UserAuthRepositoryInterface
{
    public function findByEmail(string $email): ?UserAuthData;

    public function findById(string $id): ?UserAuthData;

    public function existsByEmail(string $email): bool;

    /** Persist a brand-new user (all fields pre-built by the caller). */
    public function createUser(
        string $id,
        string $email,
        string $hashedPassword,
        string $role,
    ): void;
}
