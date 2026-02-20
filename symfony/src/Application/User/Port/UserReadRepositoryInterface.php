<?php
declare(strict_types=1);

namespace App\Application\User\Port;

use App\Application\User\DTO\UserProfileDto;

interface UserReadRepositoryInterface
{
    public function findById(string $id): ?UserProfileDto;

    /** Returns only the stored hashed password — used by ChangePasswordHandler to verify the current password. */
    public function findHashedPassword(string $id): ?string;
}
