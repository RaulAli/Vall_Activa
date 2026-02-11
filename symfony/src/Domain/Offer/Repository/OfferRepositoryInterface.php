<?php
declare(strict_types=1);

namespace App\Domain\Offer\Repository;

use App\Domain\Offer\Entity\Offer;

interface OfferRepositoryInterface
{
    public function save(Offer $offer): void;
}
