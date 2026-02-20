<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Identity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_token_blacklist')]
#[ORM\Index(name: 'idx_rtb_token_hash', columns: ['token_hash'])]
#[ORM\Index(name: 'idx_rtb_user_id', columns: ['user_id'])]
class RefreshTokenBlacklistOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    /** SHA-256 hash of the blacklisted refresh token */
    #[ORM\Column(name: 'token_hash', type: 'string', length: 64, unique: true)]
    public string $tokenHash;

    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'blacklisted_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $blacklistedAt;
}
