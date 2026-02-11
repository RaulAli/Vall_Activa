<?php
declare(strict_types=1);

namespace App\Domain\Route\Repository;

use App\Domain\Route\Entity\Route;

interface RouteRepositoryInterface
{
    public function save(Route $route): void;
}
