<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Profile\Port\FollowRepositoryInterface;
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
}
