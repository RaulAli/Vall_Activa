<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Offer;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offers')]
#[ORM\UniqueConstraint(name: 'uniq_offer_slug_business', columns: ['business_id', 'slug'])]
#[ORM\Index(name: 'idx_offer_business', columns: ['business_id'])]
#[ORM\Index(name: 'idx_offer_status', columns: ['status'])]
#[ORM\Index(name: 'idx_offer_active', columns: ['is_active'])]
class OfferOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'business_id', type: 'string', length: 36)]
    public string $businessId;

    #[ORM\Column(type: 'string', length: 255)]
    public string $title;

    #[ORM\Column(type: 'string', length: 255)]
    public string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    public string $price;

    #[ORM\Column(type: 'string', length: 3)]
    public string $currency;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    public int $quantity = 0;

    #[ORM\Column(name: 'points_cost', type: 'integer', options: ['default' => 0])]
    public int $pointsCost = 0;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    public ?string $image = null;

    #[ORM\Column(name: 'discount_type', type: 'string', length: 30)]
    public string $discountType;

    #[ORM\Column(type: 'string', length: 30)]
    public string $status;

    #[ORM\Column(name: 'admin_disabled', type: 'boolean', options: ['default' => false])]
    public bool $adminDisabled = false;
}
