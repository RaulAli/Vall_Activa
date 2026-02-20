<?php
declare(strict_types=1);

namespace App\Application\User\Command;

final class UpdateMeCommand
{
    /**
     * @param array<string, mixed> $data  Accepted keys per role:
     *   common  — slug, name, avatar
     *   business — lat, lng
     *   athlete  — birthDate, city
     *   guide    — bio, city, lat, lng, sports
     */
    public function __construct(
        public readonly string $userId,
        public readonly array $data = [],
    ) {
    }
}
