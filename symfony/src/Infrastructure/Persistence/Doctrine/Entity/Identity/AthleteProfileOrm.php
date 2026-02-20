<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Identity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'athlete_profiles')]
#[ORM\Index(name: 'idx_athlete_profiles_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_athlete_profiles_active', columns: ['is_active'])]
class AthleteProfileOrm
{
    #[ORM\Id]
    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    public string $slug;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    public ?string $avatar = null;

    #[ORM\Column(name: 'birth_date', type: 'date_immutable', nullable: true)]
    public ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $city = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
