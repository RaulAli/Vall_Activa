<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Route\Entity\Route;
use App\Domain\Route\Repository\RouteRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Mapper\RouteMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRouteRepository implements RouteRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RouteMapper $mapper
    ) {
    }

    public function save(Route $route): void
    {
        $this->em->persist($this->mapper->toOrm($route));
    }
}
