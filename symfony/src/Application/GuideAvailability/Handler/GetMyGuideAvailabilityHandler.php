<?php
declare(strict_types=1);

namespace App\Application\GuideAvailability\Handler;

use App\Application\GuideAvailability\DTO\GuideAvailabilityDto;
use App\Application\GuideAvailability\Port\GuideAvailabilityRepositoryInterface;
use App\Application\GuideAvailability\Query\GetMyGuideAvailabilityQuery;
use App\Application\User\Port\UserReadRepositoryInterface;

final class GetMyGuideAvailabilityHandler
{
    public function __construct(
        private readonly UserReadRepositoryInterface $users,
        private readonly GuideAvailabilityRepositoryInterface $availability,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(GetMyGuideAvailabilityQuery $query): GuideAvailabilityDto
    {
        $user = $this->users->findById($query->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        if ($user->role !== 'ROLE_GUIDE') {
            throw new \DomainException('forbidden_role');
        }

        return $this->availability->findByUserId($query->userId)
            ?? new GuideAvailabilityDto(
                timezone: 'UTC',
                slotMinutes: 60,
                week: UpsertMyGuideAvailabilityHandler::defaultWeek(),
            );
    }
}
