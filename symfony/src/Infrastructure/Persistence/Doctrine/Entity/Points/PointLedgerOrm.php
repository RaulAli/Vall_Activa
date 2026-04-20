<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Points;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'points_ledger')]
#[ORM\UniqueConstraint(name: 'uniq_points_ledger_source', columns: ['user_id', 'source_type', 'source_ref'])]
#[ORM\Index(name: 'idx_points_ledger_user_day', columns: ['user_id', 'day_key'])]
class PointLedgerOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(name: 'source_type', type: 'string', length: 30)]
    public string $sourceType;

    #[ORM\Column(name: 'source_ref', type: 'string', length: 120)]
    public string $sourceRef;

    #[ORM\Column(name: 'base_points', type: 'integer')]
    public int $basePoints = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    public int $multiplier = 1;

    #[ORM\Column(name: 'awarded_points', type: 'integer')]
    public int $awardedPoints = 0;

    #[ORM\Column(name: 'day_key', type: 'date_immutable')]
    public \DateTimeImmutable $dayKey;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;
}
