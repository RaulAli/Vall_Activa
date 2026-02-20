<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Identity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'guide_profiles')]
#[ORM\Index(name: 'idx_guide_profiles_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_guide_profiles_active', columns: ['is_active'])]
#[ORM\Index(name: 'idx_guide_profiles_lat_lng', columns: ['lat', 'lng'])]
class GuideProfileOrm
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

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $city = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $lng = null;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    public array $sports = [];

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
