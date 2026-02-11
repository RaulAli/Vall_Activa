<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Business;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'business_profiles')]
#[ORM\Index(name: 'idx_business_profiles_active', columns: ['is_active'])]
#[ORM\Index(name: 'idx_business_profiles_lat_lng', columns: ['lat', 'lng'])]
class BusinessProfileOrm
{
    #[ORM\Id]
    #[ORM\Column(name: 'user_id', type: 'string', length: 36)]
    public string $userId;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    public string $slug;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(name: 'profile_icon', type: 'string', length: 2048, nullable: true)]
    public ?string $profileIcon = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $lng = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
