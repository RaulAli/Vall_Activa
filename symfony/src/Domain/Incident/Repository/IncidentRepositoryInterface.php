<?php
declare(strict_types=1);

namespace App\Domain\Incident\Repository;

use App\Domain\Incident\Entity\Incident;

interface IncidentRepositoryInterface
{
    public function save(Incident $incident): void;
}
