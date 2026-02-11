<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Offer\Entity\Offer;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;

final class OfferMapper
{
    public function toOrm(Offer $offer): OfferOrm
    {
        $orm = new OfferOrm();

        $orm->id = $offer->id()->value();
        $orm->businessId = $offer->businessId();

        $orm->title = $offer->title();
        $orm->slug = $offer->slug();
        $orm->description = $offer->description();

        $orm->price = $offer->price();
        $orm->currency = $offer->currency();

        $orm->isActive = $offer->isActive();
        $orm->quantity = $offer->quantity();
        $orm->pointsCost = $offer->pointsCost();

        $orm->image = $offer->image();

        $orm->discountType = $offer->discountType()->value;
        $orm->status = $offer->status()->value;

        $now = new \DateTimeImmutable();
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        return $orm;
    }
}
