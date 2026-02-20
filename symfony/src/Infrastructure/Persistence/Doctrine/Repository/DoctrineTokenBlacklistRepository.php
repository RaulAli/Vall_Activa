<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Auth\Port\TokenBlacklistRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\RefreshTokenBlacklistOrm;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTokenBlacklistRepository implements TokenBlacklistRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function add(string $tokenHash, string $userId, \DateTimeImmutable $expiresAt): void
    {
        // Prevent duplicate inserts
        $existing = $this->em->getRepository(RefreshTokenBlacklistOrm::class)
            ->findOneBy(['tokenHash' => $tokenHash]);

        if ($existing !== null) {
            return;
        }

        $orm                = new RefreshTokenBlacklistOrm();
        $orm->id            = Uuid::v4()->value();
        $orm->tokenHash     = $tokenHash;
        $orm->userId        = $userId;
        $orm->expiresAt     = $expiresAt;
        $orm->blacklistedAt = new \DateTimeImmutable();

        $this->em->persist($orm);
        $this->em->flush();
    }

    public function isBlacklisted(string $tokenHash): bool
    {
        $orm = $this->em->getRepository(RefreshTokenBlacklistOrm::class)
            ->findOneBy(['tokenHash' => $tokenHash]);

        if ($orm === null) {
            return false;
        }

        // If the entry expired, it's effectively no longer relevant
        // (but not actively cleaned up — a cleanup job can remove old entries)
        return $orm->expiresAt > new \DateTimeImmutable();
    }
}
