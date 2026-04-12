<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Incident;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'incidents')]
#[ORM\Index(name: 'idx_incident_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_incident_status', columns: ['status'])]
#[ORM\Index(name: 'idx_incident_created_at', columns: ['created_at'])]
class IncidentOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(type: 'string', length: 50)]
    public string $category;

    #[ORM\Column(type: 'string', length: 120)]
    public string $subject;

    #[ORM\Column(type: 'text')]
    public string $message;

    #[ORM\Column(type: 'string', length: 20)]
    public string $status;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
