<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Profile;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'follows')]
#[ORM\Index(name: 'idx_follows_follower', columns: ['follower_id'])]
#[ORM\Index(name: 'idx_follows_followee', columns: ['followee_id'])]
class FollowOrm
{
    #[ORM\Id]
    #[ORM\Column(name: 'follower_id', type: 'string', length: 36)]
    public string $followerId;

    #[ORM\Id]
    #[ORM\Column(name: 'followee_id', type: 'string', length: 36)]
    public string $followeeId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;
}
