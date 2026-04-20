<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Points;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'point_settings')]
class PointSettingsOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'points_per_km', type: 'integer', options: ['default' => 50])]
    public int $pointsPerKm = 50;

    #[ORM\Column(name: 'daily_cap_athlete', type: 'integer', options: ['default' => 1000])]
    public int $dailyCapAthlete = 1000;

    #[ORM\Column(name: 'daily_cap_vip', type: 'integer', options: ['default' => 1500])]
    public int $dailyCapVip = 1500;

    #[ORM\Column(name: 'vip_multiplier', type: 'integer', options: ['default' => 2])]
    public int $vipMultiplier = 2;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
