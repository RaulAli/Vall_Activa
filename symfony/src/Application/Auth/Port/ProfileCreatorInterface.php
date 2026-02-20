<?php
declare(strict_types=1);

namespace App\Application\Auth\Port;

interface ProfileCreatorInterface
{
    /** Create the profile row for the given role (business/athlete/guide). */
    public function create(
        string $userId,
        string $role,
        string $name,
        string $slug,
    ): void;

    /**
     * Checks slug uniqueness within the given role's profile table.
     * Role values: 'ROLE_BUSINESS' | 'ROLE_ATHLETE' | 'ROLE_GUIDE'
     */
    public function existsBySlug(string $slug, string $role): bool;

    /** Returns the set of allowed role values that have a profile table. */
    public static function supportedRoles(): array;
}
