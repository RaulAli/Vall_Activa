<?php
declare(strict_types=1);

namespace App\Application\User\DTO;

final class UserProfileDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $role,
        public readonly string $createdAt,
        // Profile fields — null when the user has no profile yet
        public readonly ?string $slug = null,
        public readonly ?string $name = null,
        public readonly ?string $avatar = null,   // athlete / guide avatar; business profile_icon
        // Business + Guide
        public readonly ?float $lat = null,
        public readonly ?float $lng = null,
        // Athlete + Guide
        public readonly ?string $city = null,
        // Athlete only
        public readonly ?string $birthDate = null,
        // Guide only
        public readonly ?string $bio = null,
        /** @var list<string>|null */
        public readonly ?array $sports = null,
    ) {
    }
}
