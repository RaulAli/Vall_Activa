<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Sport;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sports')]
#[ORM\UniqueConstraint(name: 'uniq_sport_code', columns: ['code'])]
#[ORM\Index(name: 'idx_sports_active', columns: ['is_active'])]
class SportOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    public string $code;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
