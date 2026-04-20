<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Points;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'point_missions')]
#[ORM\UniqueConstraint(name: 'uniq_point_mission_code', columns: ['code'])]
#[ORM\Index(name: 'idx_point_mission_active', columns: ['is_active'])]
class PointMissionOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 80)]
    public string $code;

    #[ORM\Column(type: 'string', length: 120)]
    public string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(name: 'points_reward', type: 'integer', options: ['default' => 100])]
    public int $pointsReward = 100;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
