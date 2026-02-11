<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Route;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'routes')]
#[ORM\UniqueConstraint(name: 'uniq_route_slug_user', columns: ['created_by_user_id', 'slug'])]
#[ORM\Index(name: 'idx_route_user', columns: ['created_by_user_id'])]
#[ORM\Index(name: 'idx_route_sport', columns: ['sport_id'])]
#[ORM\Index(name: 'idx_route_status', columns: ['status'])]
#[ORM\Index(name: 'idx_route_visibility', columns: ['visibility'])]
class RouteOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'created_by_user_id', type: 'string', length: 36)]
    public string $createdByUserId;

    #[ORM\Column(name: 'sport_id', type: 'string', length: 36)]
    public string $sportId;

    #[ORM\Column(type: 'string', length: 255)]
    public string $title;

    #[ORM\Column(type: 'string', length: 255)]
    public string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: 'string', length: 30)]
    public string $visibility;

    #[ORM\Column(type: 'string', length: 30)]
    public string $status;

    #[ORM\Column(name: 'start_lat', type: 'float', nullable: true)]
    public ?float $startLat = null;

    #[ORM\Column(name: 'start_lng', type: 'float', nullable: true)]
    public ?float $startLng = null;

    #[ORM\Column(name: 'end_lat', type: 'float', nullable: true)]
    public ?float $endLat = null;

    #[ORM\Column(name: 'end_lng', type: 'float', nullable: true)]
    public ?float $endLng = null;

    #[ORM\Column(name: 'min_lat', type: 'float', nullable: true)]
    public ?float $minLat = null;

    #[ORM\Column(name: 'min_lng', type: 'float', nullable: true)]
    public ?float $minLng = null;

    #[ORM\Column(name: 'max_lat', type: 'float', nullable: true)]
    public ?float $maxLat = null;

    #[ORM\Column(name: 'max_lng', type: 'float', nullable: true)]
    public ?float $maxLng = null;

    #[ORM\Column(name: 'distance_m', type: 'integer')]
    public int $distanceM = 0;

    #[ORM\Column(name: 'elevation_gain_m', type: 'integer')]
    public int $elevationGainM = 0;

    #[ORM\Column(name: 'elevation_loss_m', type: 'integer')]
    public int $elevationLossM = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $polyline = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
