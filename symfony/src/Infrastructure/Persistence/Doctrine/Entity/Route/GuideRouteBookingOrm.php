<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Route;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'guide_route_bookings')]
#[ORM\Index(name: 'idx_guide_route_bookings_guide_slot', columns: ['guide_user_id', 'scheduled_for'])]
#[ORM\Index(name: 'idx_guide_route_bookings_route', columns: ['route_id'])]
#[ORM\Index(name: 'idx_guide_route_bookings_athlete', columns: ['athlete_user_id'])]
#[ORM\Index(name: 'idx_guide_route_bookings_status', columns: ['status'])]
class GuideRouteBookingOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'route_id', type: 'string', length: 36)]
    public string $routeId;

    #[ORM\Column(name: 'guide_user_id', type: 'string', length: 36)]
    public string $guideUserId;

    #[ORM\Column(name: 'athlete_user_id', type: 'string', length: 36)]
    public string $athleteUserId;

    #[ORM\Column(name: 'scheduled_for', type: 'datetime_immutable')]
    public \DateTimeImmutable $scheduledFor;

    #[ORM\Column(type: 'string', length: 20)]
    public string $status = 'REQUESTED';

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}
