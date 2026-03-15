<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Identity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'guide_availabilities')]
#[ORM\Index(name: 'idx_guide_availabilities_updated_at', columns: ['updated_at'])]
class GuideAvailabilityOrm
{
    #[ORM\Id]
    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(type: 'string', length: 64)]
    public string $timezone = 'UTC';

    #[ORM\Column(name: 'slot_minutes', type: 'integer', options: ['default' => 60])]
    public int $slotMinutes = 60;

    /** @var list<array{day: string, enabled: bool, slots: list<string>}> */
    #[ORM\Column(type: 'json')]
    public array $week = [];

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
