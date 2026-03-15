<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Auth\Port\TokenBlacklistRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\RefreshTokenBlacklistOrm;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTokenBlacklistRepository implements TokenBlacklistRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function add(string $tokenHash, string $userId, \DateTimeImmutable $expiresAt): void
    {
        // Use DBAL directly with ON CONFLICT DO NOTHING to handle concurrent duplicate inserts
        // atomically, without touching the ORM EntityManager state.
        $this->em->getConnection()->executeStatement(
            'INSERT INTO refresh_token_blacklist (id, token_hash, user_id, expires_at, blacklisted_at)
             VALUES (:id, :tokenHash, :userId, :expiresAt, :blacklistedAt)
             ON CONFLICT (token_hash) DO NOTHING',
            [
                'id' => Uuid::v4()->value(),
                'tokenHash' => $tokenHash,
                'userId' => $userId,
                'expiresAt' => $expiresAt->format('Y-m-d H:i:s'),
                'blacklistedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]
        );
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
