<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\Query;

final class GetMyGuideAvailabilityQuery
{
    public function __construct(
        public readonly string $userId,
    ) {
    }
}
