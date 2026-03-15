<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\Port;

use App\Application\GuideAvailability\DTO\GuideAvailabilityDto;

interface GuideAvailabilityRepositoryInterface
{
    public function findByUserId(string $userId): ?GuideAvailabilityDto;

    public function upsert(string $userId, GuideAvailabilityDto $availability): GuideAvailabilityDto;
}
