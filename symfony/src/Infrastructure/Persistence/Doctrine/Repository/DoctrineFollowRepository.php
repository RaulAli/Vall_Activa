<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Profile\Port\FollowRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Profile\FollowOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineFollowRepository implements FollowRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function follow(string $followerId, string $followeeId): void
    {
        $orm = new FollowOrm();
        $orm->followerId = $followerId;
        $orm->followeeId = $followeeId;
        $orm->createdAt = new \DateTimeImmutable();

        $this->em->persist($orm);
        $this->em->flush();
    }

    public function unfollow(string $followerId, string $followeeId): void
    {
        $orm = $this->em->find(FollowOrm::class, [
            'followerId' => $followerId,
            'followeeId' => $followeeId,
        ]);

        if ($orm !== null) {
            $this->em->remove($orm);
            $this->em->flush();
        }
    }

    public function isFollowing(string $followerId, string $followeeId): bool
    {
        return $this->em->find(FollowOrm::class, [
            'followerId' => $followerId,
            'followeeId' => $followeeId,
        ]) !== null;
    }

    public function countFollowers(string $followeeId): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(f.followerId)')
            ->from(FollowOrm::class, 'f')
            ->where('f.followeeId = :id')
            ->setParameter('id', $followeeId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFollowing(string $followerId): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(f.followeeId)')
            ->from(FollowOrm::class, 'f')
            ->where('f.followerId = :id')
            ->setParameter('id', $followerId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function listFollowers(string $followeeId): array
    {
        $ids = $this->em->createQueryBuilder()
            ->select('f.followerId')
            ->from(FollowOrm::class, 'f')
            ->where('f.followeeId = :id')
            ->setParameter('id', $followeeId)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->resolveProfiles($ids);
    }

    public function listFollowing(string $followerId): array
    {
        $ids = $this->em->createQueryBuilder()
            ->select('f.followeeId')
            ->from(FollowOrm::class, 'f')
            ->where('f.followerId = :id')
            ->setParameter('id', $followerId)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->resolveProfiles($ids);
    }

    /** @return array<int, array{slug:string, name:string, avatar:string|null, role:string}> */
    private function resolveProfiles(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $map = [];

        $businesses = $this->em->createQueryBuilder()
            ->select('b')
            ->from(BusinessProfileOrm::class, 'b')
            ->where('b.userId IN (:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()->getResult();
        foreach ($businesses as $b) {
            $map[$b->userId] = ['slug' => $b->slug, 'name' => $b->name, 'avatar' => $b->profileIcon, 'role' => 'ROLE_BUSINESS'];
        }

        $athletes = $this->em->createQueryBuilder()
            ->select('a')
            ->from(AthleteProfileOrm::class, 'a')
            ->where('a.userId IN (:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()->getResult();
        foreach ($athletes as $a) {
            $map[$a->userId] = ['slug' => $a->slug, 'name' => $a->name, 'avatar' => $a->avatar, 'role' => 'ROLE_ATHLETE'];
        }

        $guides = $this->em->createQueryBuilder()
            ->select('g')
            ->from(GuideProfileOrm::class, 'g')
            ->where('g.userId IN (:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()->getResult();
        foreach ($guides as $g) {
            $map[$g->userId] = ['slug' => $g->slug, 'name' => $g->name, 'avatar' => $g->avatar, 'role' => 'ROLE_GUIDE'];
        }

        return array_values(array_filter(array_map(fn ($id) => $map[$id] ?? null, $userIds)));
    }
}
