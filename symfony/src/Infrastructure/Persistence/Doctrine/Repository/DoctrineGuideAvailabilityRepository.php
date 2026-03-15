<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\GuideAvailability\DTO\GuideAvailabilityDto;
use App\Application\GuideAvailability\Port\GuideAvailabilityRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideAvailabilityOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineGuideAvailabilityRepository implements GuideAvailabilityRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findByUserId(string $userId): ?GuideAvailabilityDto
    {
        $orm = $this->em->find(GuideAvailabilityOrm::class, $userId);
        if ($orm === null) {
            return null;
        }

        return new GuideAvailabilityDto(
            timezone: $orm->timezone,
            slotMinutes: $orm->slotMinutes,
            week: $orm->week,
        );
    }

    public function upsert(string $userId, GuideAvailabilityDto $availability): GuideAvailabilityDto
    {
        $now = new \DateTimeImmutable();

        $orm = $this->em->find(GuideAvailabilityOrm::class, $userId);
        if ($orm === null) {
            $orm = new GuideAvailabilityOrm();
            $orm->userId = $userId;
            $orm->createdAt = $now;
            $this->em->persist($orm);
        }

        $orm->timezone = $availability->timezone;
        $orm->slotMinutes = 60;
        $orm->week = $availability->week;
        $orm->updatedAt = $now;

        $this->em->flush();

        return new GuideAvailabilityDto(
            timezone: $orm->timezone,
            slotMinutes: $orm->slotMinutes,
            week: $orm->week,
        );
    }
}
