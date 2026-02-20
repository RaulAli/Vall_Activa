<?php
declare(strict_types=1);

namespace App\Application\User\Port;

interface UserWriteRepositoryInterface
{
    /**
     * Update the profile row for the given role.
     * Only keys present in $data are updated (slug, name, avatar, city, lat, lng, birthDate, bio, sports).
     */
    public function updateProfile(string $userId, string $role, array $data): void;

    public function changePassword(string $id, string $newHashedPassword): void;

    /** Slug uniqueness within the same role's profile table, excluding the given userId. */
    public function existsBySlug(string $slug, string $role, string $excludeUserId): bool;
}
