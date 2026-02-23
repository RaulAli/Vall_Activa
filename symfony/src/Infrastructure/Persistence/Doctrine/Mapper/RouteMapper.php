<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Route\Entity\Route;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;

final class RouteMapper
{
    public function toOrm(Route $route): RouteOrm
    {
        $orm = new RouteOrm();
        $orm->id = $route->id()->value();
        $orm->createdByUserId = $route->createdByUserId();
        $orm->sportId = $route->sportId()->value();

        $orm->title = $route->title();
        $orm->slug = $route->slug();
        $orm->description = $route->description();

        $orm->visibility = $route->visibility()->value;
        $orm->status = $route->status()->value;

        $orm->startLat = $route->startLat();
        $orm->startLng = $route->startLng();
        $orm->endLat = $route->endLat();
        $orm->endLng = $route->endLng();
        $orm->minLat = $route->minLat();
        $orm->minLng = $route->minLng();
        $orm->maxLat = $route->maxLat();
        $orm->maxLng = $route->maxLng();

        $orm->polyline = $route->polyline(); // ✅ NUEVO
        $orm->image = $route->image();

        $orm->distanceM = $route->distanceM();
        $orm->elevationGainM = $route->elevationGainM();
        $orm->elevationLossM = $route->elevationLossM();
        $orm->durationSeconds = $route->durationSeconds();

        $orm->isActive = $route->isActive();

        $now = new \DateTimeImmutable();
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        return $orm;
    }
}
