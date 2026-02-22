<?php
declare(strict_types=1);

namespace App\Application\Profile\DTO;

final class PublicProfileDto
{
    public function __construct(
        public readonly string $userId,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $avatar,
        public readonly string $role,
        public readonly int $followersCount,
        public readonly bool $isFollowedByMe,
        // Role-specific (null when irrelevant)
        public readonly ?float $lat = null,
        public readonly ?float $lng = null,
        public readonly ?string $city = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $bio = null,
        public readonly ?array $sports = null,
    ) {
    }
}
