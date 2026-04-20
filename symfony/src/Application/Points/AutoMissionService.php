<?php
declare(strict_types=1);

namespace App\Application\Points;

use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionCompletionOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;

final class AutoMissionService
{
    public const CODE_DAILY_10KM = 'DAILY_10KM';
    public const CODE_FIRST_ROUTE_UPLOAD_DAILY = 'FIRST_ROUTE_UPLOAD_DAILY';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PointsEngine $pointsEngine,
    ) {
    }

    /**
     * @return array{completedToday:bool,currentKm:float,targetKm:float}
     */
    public function syncDaily10Km(UserOrm $user): array
    {
        $today = new \DateTimeImmutable('today');
        $mission = $this->em->getRepository(PointMissionOrm::class)->findOneBy([
            'code' => self::CODE_DAILY_10KM,
            'isActive' => true,
        ]);

        if (!$mission instanceof PointMissionOrm) {
            return ['completedToday' => false, 'currentKm' => 0.0, 'targetKm' => 10.0];
        }

        $currentKm = $this->getTodayCompletedKm($user->id);
        $completed = $this->hasCompletionToday($user->id, $mission->id, $today);

        if (!$completed && $currentKm >= 10.0) {
            $this->completeMission($user, $mission, $today);
            $completed = true;
        }

        return ['completedToday' => $completed, 'currentKm' => $currentKm, 'targetKm' => 10.0];
    }

    /**
     * @return array{completedToday:bool,currentCount:int,targetCount:int}
     */
    public function syncFirstRouteUploadDaily(UserOrm $user): array
    {
        $today = new \DateTimeImmutable('today');
        $mission = $this->em->getRepository(PointMissionOrm::class)->findOneBy([
            'code' => self::CODE_FIRST_ROUTE_UPLOAD_DAILY,
            'isActive' => true,
        ]);

        if (!$mission instanceof PointMissionOrm) {
            return ['completedToday' => false, 'currentCount' => 0, 'targetCount' => 1];
        }

        $count = $this->getTodayCreatedRoutesCount($user->id);
        $completed = $this->hasCompletionToday($user->id, $mission->id, $today);

        if (!$completed && $count >= 1) {
            $this->completeMission($user, $mission, $today);
            $completed = true;
        }

        return ['completedToday' => $completed, 'currentCount' => $count, 'targetCount' => 1];
    }

    public function syncAll(UserOrm $user): void
    {
        $this->syncDaily10Km($user);
        $this->syncFirstRouteUploadDaily($user);
    }

    private function hasCompletionToday(string $userId, string $missionId, \DateTimeImmutable $today): bool
    {
        $existing = $this->em->getRepository(PointMissionCompletionOrm::class)->findOneBy([
            'userId' => $userId,
            'missionId' => $missionId,
            'dayKey' => $today,
        ]);

        return $existing instanceof PointMissionCompletionOrm;
    }

    private function completeMission(UserOrm $user, PointMissionOrm $mission, \DateTimeImmutable $today): void
    {
        $result = $this->pointsEngine->award(
            user: $user,
            basePoints: $mission->pointsReward,
            sourceType: 'MISSION',
            sourceRef: $mission->id . ':' . $today->format('Y-m-d'),
        );

        $completion = new PointMissionCompletionOrm();
        $completion->id = Uuid::v4()->value();
        $completion->userId = $user->id;
        $completion->missionId = $mission->id;
        $completion->dayKey = $today;
        $completion->awardedPoints = $result['awarded'];
        $completion->createdAt = new \DateTimeImmutable();

        $this->em->persist($completion);
    }

    private function getTodayCompletedKm(string $athleteUserId): float
    {
        $connection = $this->em->getConnection();

        $bookingMeters = (int) $connection->fetchOne(
            "SELECT COALESCE(SUM(r.distance_m), 0)
             FROM guide_route_bookings b
             INNER JOIN routes r ON r.id = b.route_id
             WHERE b.athlete_user_id = :userId
               AND b.status = 'COMPLETED'
               AND b.payment_status = 'PAID'
               AND b.updated_at >= CURRENT_DATE
               AND b.updated_at < (CURRENT_DATE + INTERVAL '1 day')",
            ['userId' => $athleteUserId]
        );

        $uploadedMeters = (int) $connection->fetchOne(
            "SELECT COALESCE(SUM(r.distance_m), 0)
             FROM routes r
             WHERE r.created_by_user_id = :userId
               AND r.created_at >= CURRENT_DATE
               AND r.created_at < (CURRENT_DATE + INTERVAL '1 day')",
            ['userId' => $athleteUserId]
        );

        return round(($bookingMeters + $uploadedMeters) / 1000, 2);
    }

    private function getTodayCreatedRoutesCount(string $userId): int
    {
        return (int) $this->em->getConnection()->fetchOne(
            "SELECT COUNT(*)
             FROM routes r
             WHERE r.created_by_user_id = :userId
               AND r.created_at >= CURRENT_DATE
               AND r.created_at < (CURRENT_DATE + INTERVAL '1 day')",
            ['userId' => $userId]
        );
    }
}
