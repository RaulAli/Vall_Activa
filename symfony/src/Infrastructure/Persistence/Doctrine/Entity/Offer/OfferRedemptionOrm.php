<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity\Offer;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offer_redemptions')]
#[ORM\Index(name: 'idx_offer_redemptions_offer', columns: ['offer_id'])]
#[ORM\Index(name: 'idx_offer_redemptions_athlete', columns: ['athlete_user_id'])]
class OfferRedemptionOrm
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'offer_id', type: 'string', length: 36)]
    public string $offerId;

    #[ORM\Column(name: 'athlete_user_id', type: 'string', length: 36)]
    public string $athleteUserId;

    #[ORM\Column(name: 'points_spent', type: 'integer')]
    public int $pointsSpent = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;
}
