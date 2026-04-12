<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Incident;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'incident_categories')]
#[ORM\UniqueConstraint(name: 'uniq_incident_category_code', columns: ['code'])]
#[ORM\Index(name: 'idx_incident_category_active', columns: ['is_active'])]
class IncidentCategoryOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 30, unique: true)]
    public string $code;

    #[ORM\Column(type: 'string', length: 80)]
    public string $name;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
