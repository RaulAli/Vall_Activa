<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\Command;

final class UpsertMyGuideAvailabilityCommand
{
    /** @param array<int, mixed> $week */
    public function __construct(
        public readonly string $userId,
        public readonly ?string $timezone,
        public readonly array $week,
    ) {
    }
}
