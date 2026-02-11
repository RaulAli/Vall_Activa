<?php
declare(strict_types=1);

namespace App\Domain\Sport\Repository;

use App\Domain\Sport\Entity\Sport;
use App\Domain\Sport\ValueObject\SportCode;

interface SportRepositoryInterface
{
    public function findActiveByCode(SportCode $code): ?Sport;
}
