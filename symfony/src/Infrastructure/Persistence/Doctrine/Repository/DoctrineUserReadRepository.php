<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserReadRepository
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function find(string $id): ?UserOrm
    {
        return $this->em->find(UserOrm::class, $id);
    }
}
