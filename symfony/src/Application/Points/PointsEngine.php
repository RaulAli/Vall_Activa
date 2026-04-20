<?php
declare(strict_types=1);

namespace App\Application\Points;

use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointLedgerOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointSettingsOrm;
use Doctrine\ORM\EntityManagerInterface;

final class PointsEngine
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function getSettings(): PointSettingsOrm
    {
        $settings = $this->em->getRepository(PointSettingsOrm::class)->findOneBy([]);
        if ($settings instanceof PointSettingsOrm) {
            return $settings;
        }

        $now = new \DateTimeImmutable();
        $settings = new PointSettingsOrm();
        $settings->id = Uuid::v4()->value();
        $settings->pointsPerKm = 50;
        $settings->dailyCapAthlete = 1000;
        $settings->dailyCapVip = 1500;
        $settings->vipMultiplier = 2;
        $settings->createdAt = $now;
        $settings->updatedAt = $now;

        $this->em->persist($settings);
        $this->em->flush();

        return $settings;
    }

    public function isVipForPoints(UserOrm $user, ?\DateTimeImmutable $at = null): bool
    {
        if (!$user->vipActive) {
            return false;
        }

        if ($user->vipExpiresAt === null) {
            return true;
        }

        $at ??= new \DateTimeImmutable();

        return $user->vipExpiresAt >= $at;
    }

    public function getDailyCap(UserOrm $user): int
    {
        $settings = $this->getSettings();

        return $this->isVipForPoints($user) ? $settings->dailyCapVip : $settings->dailyCapAthlete;
    }

    public function getMultiplier(UserOrm $user): int
    {
        $settings = $this->getSettings();
        if (!$this->isVipForPoints($user)) {
            return 1;
        }

        return max(1, $settings->vipMultiplier);
    }

    public function getTodayEarned(string $userId, ?\DateTimeImmutable $day = null): int
    {
        $day ??= new \DateTimeImmutable('today');

        return (int) $this->em->getConnection()->fetchOne(
            'SELECT COALESCE(SUM(awarded_points), 0) FROM points_ledger WHERE user_id = :userId AND day_key = :dayKey',
            [
                'userId' => $userId,
                'dayKey' => $day->format('Y-m-d'),
            ]
        );
    }

    /**
     * @return array{awarded:int, requested:int, today:int, cap:int, multiplier:int, alreadyAwarded:bool}
     */
    public function award(
        UserOrm $user,
        int $basePoints,
        string $sourceType,
        string $sourceRef,
        ?\DateTimeImmutable $awardedAt = null,
    ): array {
        $awardedAt ??= new \DateTimeImmutable();
        $day = $awardedAt->setTime(0, 0);
        $multiplier = $this->getMultiplier($user);
        $cap = $this->getDailyCap($user);

        $already = $this->em->getRepository(PointLedgerOrm::class)->findOneBy([
            'userId' => $user->id,
            'sourceType' => $sourceType,
            'sourceRef' => $sourceRef,
        ]);

        if ($already instanceof PointLedgerOrm) {
            $today = $this->getTodayEarned($user->id, $day);

            return [
                'awarded' => 0,
                'requested' => max(0, $basePoints * $multiplier),
                'today' => $today,
                'cap' => $cap,
                'multiplier' => $multiplier,
                'alreadyAwarded' => true,
            ];
        }

        $requested = max(0, $basePoints * $multiplier);
        $today = $this->getTodayEarned($user->id, $day);
        $remaining = max(0, $cap - $today);
        $awarded = min($requested, $remaining);

        if ($awarded > 0) {
            $entry = new PointLedgerOrm();
            $entry->id = Uuid::v4()->value();
            $entry->userId = $user->id;
            $entry->sourceType = $sourceType;
            $entry->sourceRef = $sourceRef;
            $entry->basePoints = max(0, $basePoints);
            $entry->multiplier = $multiplier;
            $entry->awardedPoints = $awarded;
            $entry->dayKey = $day;
            $entry->createdAt = $awardedAt;

            $this->em->persist($entry);
            $user->pointsBalance += $awarded;
            $today += $awarded;
        }

        return [
            'awarded' => $awarded,
            'requested' => $requested,
            'today' => $today,
            'cap' => $cap,
            'multiplier' => $multiplier,
            'alreadyAwarded' => false,
        ];
    }
}
