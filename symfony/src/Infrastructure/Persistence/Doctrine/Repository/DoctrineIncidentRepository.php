<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Incident\Entity\Incident;
use App\Domain\Incident\Repository\IncidentRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Mapper\IncidentMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineIncidentRepository implements IncidentRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IncidentMapper $mapper,
    ) {
    }

    public function save(Incident $incident): void
    {
        $orm = $this->mapper->toOrm($incident);
        $this->em->persist($orm);
    }
}
