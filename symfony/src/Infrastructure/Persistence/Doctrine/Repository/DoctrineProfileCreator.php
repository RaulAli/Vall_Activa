<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Auth\Port\ProfileCreatorInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProfileCreator implements ProfileCreatorInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function create(string $userId, string $role, string $name, string $slug, array $data = []): void
    {
        $now = new \DateTimeImmutable();

        match ($role) {
            'ROLE_BUSINESS' => $this->createBusiness($userId, $name, $slug, $now),
            'ROLE_ATHLETE' => $this->createAthlete($userId, $name, $slug, $now),
            'ROLE_GUIDE' => $this->createGuide($userId, $name, $slug, $now, $data),
            default => null,
        };
    }

    public function existsBySlug(string $slug, string $role): bool
    {
        $ormClass = $this->ormClassFor($role);
        if ($ormClass === null) {
            return false;
        }

        return $this->em->createQueryBuilder()
            ->select('COUNT(p.userId)')
            ->from($ormClass, 'p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public static function supportedRoles(): array
    {
        return ['ROLE_BUSINESS', 'ROLE_ATHLETE', 'ROLE_GUIDE'];
    }

    // ── private helpers ───────────────────────────────────────────────────────

    private function createBusiness(string $userId, string $name, string $slug, \DateTimeImmutable $now): void
    {
        $orm = new BusinessProfileOrm();
        $orm->userId = $userId;
        $orm->name = $name;
        $orm->slug = $slug;
        $orm->profileIcon = 'https://robohash.org/' . urlencode($slug) . '.png';
        $orm->isActive = true;
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        $this->em->persist($orm);
        $this->em->flush();
    }

    private function createAthlete(string $userId, string $name, string $slug, \DateTimeImmutable $now): void
    {
        $orm = new AthleteProfileOrm();
        $orm->userId = $userId;
        $orm->name = $name;
        $orm->slug = $slug;
        $orm->avatar = 'https://robohash.org/' . urlencode($slug) . '.png';
        $orm->isActive = true;
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        $this->em->persist($orm);
        $this->em->flush();
    }

    private function createGuide(string $userId, string $name, string $slug, \DateTimeImmutable $now, array $data): void
    {
        $orm = new GuideProfileOrm();
        $orm->userId = $userId;
        $orm->name = $name;
        $orm->slug = $slug;
        $orm->avatar = 'https://robohash.org/' . urlencode($slug) . '.png';
        $orm->bio = isset($data['bio']) && is_string($data['bio']) ? trim($data['bio']) ?: null : null;
        $orm->city = isset($data['city']) && is_string($data['city']) ? trim($data['city']) ?: null : null;
        $sports = isset($data['sports']) && is_array($data['sports']) ? array_values(array_map('strval', $data['sports'])) : [];
        $orm->sports = array_values(array_unique($sports));
        $orm->isVerified = false;
        $orm->isActive = true;
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        $this->em->persist($orm);
        $this->em->flush();
    }

    private function ormClassFor(string $role): ?string
    {
        return match ($role) {
            'ROLE_BUSINESS' => BusinessProfileOrm::class,
            'ROLE_ATHLETE' => AthleteProfileOrm::class,
            'ROLE_GUIDE' => GuideProfileOrm::class,
            default => null,
        };
    }
}
