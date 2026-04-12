<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Incident\Port\IncidentCategoryReadRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentCategoryOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineIncidentCategoryReadRepository implements IncidentCategoryReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function isActiveCode(string $code): bool
    {
        if ($code === '') {
            return false;
        }

        $found = $this->em->getRepository(IncidentCategoryOrm::class)->findOneBy([
            'code' => strtoupper($code),
            'isActive' => true,
        ]);

        return $found !== null;
    }

    public function listActive(): array
    {
        /** @var IncidentCategoryOrm[] $categories */
        $categories = $this->em->getRepository(IncidentCategoryOrm::class)->findBy(
            ['isActive' => true],
            ['name' => 'ASC'],
        );

        return array_map(static fn(IncidentCategoryOrm $c): array => [
            'code' => $c->code,
            'name' => $c->name,
        ], $categories);
    }
}
