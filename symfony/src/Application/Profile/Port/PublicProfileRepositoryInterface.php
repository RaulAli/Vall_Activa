<?php
declare(strict_types=1);

namespace App\Application\Profile\Port;

interface PublicProfileRepositoryInterface
{
    /**
     * Search for a profile by slug across all profile tables (business, athlete, guide).
     *
     * Returns an associative array with keys:
     *   userId, slug, name, avatar, role,
     *   and role-specific keys: lat, lng, city, birthDate, bio, sports
     *
     * Returns null if not found.
     *
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array;
}
