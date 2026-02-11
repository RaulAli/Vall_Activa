<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Offer\Entity\Offer;
use App\Domain\Offer\Repository\OfferRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Mapper\OfferMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOfferRepository implements OfferRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OfferMapper $mapper
    ) {}

    public function save(Offer $offer): void
    {
        $orm = $this->mapper->toOrm($offer);
        $this->em->persist($orm);
        // flush lo controla Transaction
    }
}
