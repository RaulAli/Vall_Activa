<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Auth\Port\RefreshSessionRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\RefreshSessionOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRefreshSessionRepository implements RefreshSessionRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(array $data): void
    {
        $now = new \DateTimeImmutable();

        $orm                   = new RefreshSessionOrm();
        $orm->id               = $data['id'];
        $orm->userId           = $data['userId'];
        $orm->deviceId         = $data['deviceId'];
        $orm->familyId         = $data['familyId'];
        $orm->currentTokenHash = $data['tokenHash'];
        $orm->sessionVersion   = $data['sessionVersion'];
        $orm->revoked          = false;
        $orm->expiresAt        = $data['expiresAt'];
        $orm->createdAt        = $now;
        $orm->updatedAt        = $now;

        $this->em->persist($orm);
        $this->em->flush();
    }

    public function findActiveByTokenHash(string $hash): ?array
    {
        $orm = $this->em->getRepository(RefreshSessionOrm::class)->findOneBy([
            'currentTokenHash' => $hash,
            'revoked'          => false,
        ]);

        if ($orm === null) {
            return null;
        }

        return [
            'id'             => $orm->id,
            'userId'         => $orm->userId,
            'deviceId'       => $orm->deviceId,
            'familyId'       => $orm->familyId,
            'sessionVersion' => $orm->sessionVersion,
            'expiresAt'      => $orm->expiresAt,
        ];
    }

    public function rotateToken(string $sessionId, string $newHash, \DateTimeImmutable $newExpiresAt): void
    {
        $orm = $this->em->find(RefreshSessionOrm::class, $sessionId);
        if ($orm === null) {
            return;
        }

        $orm->currentTokenHash = $newHash;
        $orm->expiresAt        = $newExpiresAt;
        $orm->updatedAt        = new \DateTimeImmutable();

        $this->em->flush();
    }

    public function revoke(string $sessionId): void
    {
        $orm = $this->em->find(RefreshSessionOrm::class, $sessionId);
        if ($orm === null) {
            return;
        }

        $orm->revoked    = true;
        $orm->updatedAt  = new \DateTimeImmutable();

        $this->em->flush();
    }

    public function revokeAllForUser(string $userId): void
    {
        $this->em->createQueryBuilder()
            ->update(RefreshSessionOrm::class, 's')
            ->set('s.revoked', ':true')
            ->set('s.updatedAt', ':now')
            ->where('s.userId = :userId')
            ->andWhere('s.revoked = :false')
            ->setParameter('true', true)
            ->setParameter('false', false)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    public function findRevokedByTokenHash(string $hash): ?array
    {
        $orm = $this->em->getRepository(RefreshSessionOrm::class)->findOneBy([
            'currentTokenHash' => $hash,
            'revoked'          => true,
        ]);

        if ($orm === null) {
            return null;
        }

        return [
            'id'       => $orm->id,
            'userId'   => $orm->userId,
            'familyId' => $orm->familyId,
        ];
    }

    public function revokeByFamily(string $familyId): void
    {
        $this->em->createQueryBuilder()
            ->update(RefreshSessionOrm::class, 's')
            ->set('s.revoked', ':true')
            ->set('s.updatedAt', ':now')
            ->where('s.familyId = :familyId')
            ->setParameter('true', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('familyId', $familyId)
            ->getQuery()
            ->execute();
    }

    public function findById(string $sessionId): ?array
    {
        $orm = $this->em->find(RefreshSessionOrm::class, $sessionId);
        if ($orm === null) {
            return null;
        }

        return [
            'id'             => $orm->id,
            'userId'         => $orm->userId,
            'sessionVersion' => $orm->sessionVersion,
            'revoked'        => $orm->revoked,
        ];
    }

    public function revokeAndIncrementVersionForUser(string $userId): void
    {
        $this->em->createQueryBuilder()
            ->update(RefreshSessionOrm::class, 's')
            ->set('s.revoked', ':true')
            ->set('s.sessionVersion', 's.sessionVersion + 1')
            ->set('s.updatedAt', ':now')
            ->where('s.userId = :userId')
            ->andWhere('s.revoked = :false')
            ->setParameter('true', true)
            ->setParameter('false', false)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
