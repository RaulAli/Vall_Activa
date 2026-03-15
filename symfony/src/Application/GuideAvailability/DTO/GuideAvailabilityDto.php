<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\DTO;

final class GuideAvailabilityDto
{
    /**
     * @param list<array{day: string, enabled: bool, slots: list<string>}> $week
     */
    public function __construct(
        public readonly string $timezone,
        public readonly int $slotMinutes,
        public readonly array $week,
    ) {
    }
}
