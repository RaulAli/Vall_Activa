<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Identity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_sessions')]
#[ORM\Index(name: 'idx_rs_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_rs_device_id', columns: ['device_id'])]
#[ORM\Index(name: 'idx_rs_family_id', columns: ['family_id'])]
class RefreshSessionOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(name: 'device_id', type: 'string', length: 36)]
    public string $deviceId;

    /** Groups all rotations that belong to the same login chain */
    #[ORM\Column(name: 'family_id', type: 'string', length: 36)]
    public string $familyId;

    /** SHA-256 hash of the raw refresh token */
    #[ORM\Column(name: 'current_token_hash', type: 'string', length: 64)]
    public string $currentTokenHash;

    #[ORM\Column(name: 'revoked', type: 'boolean', options: ['default' => false])]
    public bool $revoked = false;

    /** Incremented on forced logout / security event to invalidate all tokens at once */
    #[ORM\Column(name: 'session_version', type: 'integer', options: ['default' => 1])]
    public int $sessionVersion = 1;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
