<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\User\Port\UserWriteRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserWriteRepository implements UserWriteRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function updateProfile(string $userId, string $role, array $data): void
    {
        $now = new \DateTimeImmutable();

        match ($role) {
            'ROLE_BUSINESS' => $this->updateBusiness($userId, $data, $now),
            'ROLE_ATHLETE' => $this->updateAthlete($userId, $data, $now),
            'ROLE_GUIDE' => $this->updateGuide($userId, $data, $now),
            default => null,
        };
    }

    public function changePassword(string $id, string $newHashedPassword): void
    {
        $orm = $this->em->find(UserOrm::class, $id);
        if ($orm === null) {
            return;
        }

        $orm->password = $newHashedPassword;
        $orm->updatedAt = new \DateTimeImmutable();

        $this->em->flush();
    }

    public function existsBySlug(string $slug, string $role, string $excludeUserId): bool
    {
        $ormClass = $this->ormClassFor($role);
        if ($ormClass === null) {
            return false;
        }

        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(p.userId)')
            ->from($ormClass, 'p')
            ->where('p.slug = :slug')
            ->andWhere('p.userId != :exclude')
            ->setParameter('slug', $slug)
            ->setParameter('exclude', $excludeUserId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    // ── private helpers ───────────────────────────────────────────────────────

    private function updateBusiness(string $userId, array $data, \DateTimeImmutable $now): void
    {
        $orm = $this->em->find(BusinessProfileOrm::class, $userId);
        if ($orm === null) {
            return;
        }

        if (isset($data['name']))
            $orm->name = (string) $data['name'];
        if (isset($data['slug']))
            $orm->slug = (string) $data['slug'];
        if (array_key_exists('avatar', $data)) {
            $orm->profileIcon = ($data['avatar'] === '' || $data['avatar'] === null) ? null : (string) $data['avatar'];
        }
        if (array_key_exists('lat', $data))
            $orm->lat = $data['lat'] !== null ? (float) $data['lat'] : null;
        if (array_key_exists('lng', $data))
            $orm->lng = $data['lng'] !== null ? (float) $data['lng'] : null;

        $orm->updatedAt = $now;
        $this->em->flush();
    }

    private function updateAthlete(string $userId, array $data, \DateTimeImmutable $now): void
    {
        $orm = $this->em->find(AthleteProfileOrm::class, $userId);
        if ($orm === null) {
            return;
        }

        if (isset($data['name']))
            $orm->name = (string) $data['name'];
        if (isset($data['slug']))
            $orm->slug = (string) $data['slug'];
        if (isset($data['city']))
            $orm->city = (string) $data['city'];
        if (array_key_exists('avatar', $data)) {
            $orm->avatar = ($data['avatar'] === '' || $data['avatar'] === null) ? null : (string) $data['avatar'];
        }
        if (isset($data['birthDate']) && $data['birthDate'] !== '') {
            $orm->birthDate = new \DateTimeImmutable((string) $data['birthDate']);
        }

        $orm->updatedAt = $now;
        $this->em->flush();
    }

    private function updateGuide(string $userId, array $data, \DateTimeImmutable $now): void
    {
        $orm = $this->em->find(GuideProfileOrm::class, $userId);
        if ($orm === null) {
            return;
        }

        if (isset($data['name']))
            $orm->name = (string) $data['name'];
        if (isset($data['slug']))
            $orm->slug = (string) $data['slug'];
        if (isset($data['bio']))
            $orm->bio = (string) $data['bio'];
        if (isset($data['city']))
            $orm->city = (string) $data['city'];
        if (array_key_exists('avatar', $data)) {
            $orm->avatar = ($data['avatar'] === '' || $data['avatar'] === null) ? null : (string) $data['avatar'];
        }
        if (array_key_exists('lat', $data))
            $orm->lat = $data['lat'] !== null ? (float) $data['lat'] : null;
        if (array_key_exists('lng', $data))
            $orm->lng = $data['lng'] !== null ? (float) $data['lng'] : null;
        if (isset($data['sports']) && is_array($data['sports'])) {
            $orm->sports = array_values(array_map('strval', $data['sports']));
        }

        $orm->updatedAt = $now;
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
