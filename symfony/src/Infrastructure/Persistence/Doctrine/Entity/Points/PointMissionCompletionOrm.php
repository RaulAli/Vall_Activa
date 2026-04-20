<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Points;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'point_mission_completions')]
#[ORM\UniqueConstraint(name: 'uniq_point_mission_completion_day', columns: ['user_id', 'mission_id', 'day_key'])]
#[ORM\Index(name: 'idx_point_mission_completion_user_day', columns: ['user_id', 'day_key'])]
class PointMissionCompletionOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(name: 'mission_id', type: 'string', length: 36)]
    public string $missionId;

    #[ORM\Column(name: 'day_key', type: 'date_immutable')]
    public \DateTimeImmutable $dayKey;

    #[ORM\Column(name: 'awarded_points', type: 'integer')]
    public int $awardedPoints = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;
}
