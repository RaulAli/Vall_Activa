<?php
declare(strict_types=1);

namespace App\Domain\Route\Entity;

use App\Domain\Route\ValueObject\RouteId;
use App\Domain\Route\ValueObject\RouteStatus;
use App\Domain\Route\ValueObject\RouteVisibility;
use App\Domain\Sport\ValueObject\SportId;

final class Route
{
    private function __construct(
        private RouteId $id,
        private string $createdByUserId,
        private SportId $sportId,

        private string $title,
        private string $slug,
        private ?string $description,

        private RouteVisibility $visibility,
        private RouteStatus $status,

        private ?float $startLat,
        private ?float $startLng,
        private ?float $endLat,
        private ?float $endLng,
        private ?float $minLat,
        private ?float $minLng,
        private ?float $maxLat,
        private ?float $maxLng,

        private ?string $polyline,

        private int $distanceM,
        private int $elevationGainM,
        private int $elevationLossM,

        private bool $isActive
    ) {
    }

    public static function createFromParsed(
        string $createdByUserId,
        SportId $sportId,
        string $title,
        string $slug,
        ?string $description,
        RouteVisibility $visibility,
        RouteStatus $status,
        ?float $startLat,
        ?float $startLng,
        ?float $endLat,
        ?float $endLng,
        ?float $minLat,
        ?float $minLng,
        ?float $maxLat,
        ?float $maxLng,
        ?string $polyline,            // ✅ NUEVO
        int $distanceM,
        int $elevationGainM,
        int $elevationLossM
    ): self {
        if ($distanceM < 0) {
            throw new \InvalidArgumentException('Distance cannot be negative.');
        }

        return new self(
            RouteId::new(),
            $createdByUserId,
            $sportId,
            $title,
            $slug,
            $description,
            $visibility,
            $status,
            $startLat,
            $startLng,
            $endLat,
            $endLng,
            $minLat,
            $minLng,
            $maxLat,
            $maxLng,
            $polyline,                 // ✅ NUEVO
            $distanceM,
            $elevationGainM,
            $elevationLossM,
            true
        );
    }

    public function id(): RouteId
    {
        return $this->id;
    }
    public function createdByUserId(): string
    {
        return $this->createdByUserId;
    }
    public function sportId(): SportId
    {
        return $this->sportId;
    }

    public function title(): string
    {
        return $this->title;
    }
    public function slug(): string
    {
        return $this->slug;
    }
    public function description(): ?string
    {
        return $this->description;
    }

    public function visibility(): RouteVisibility
    {
        return $this->visibility;
    }
    public function status(): RouteStatus
    {
        return $this->status;
    }

    public function startLat(): ?float
    {
        return $this->startLat;
    }
    public function startLng(): ?float
    {
        return $this->startLng;
    }
    public function endLat(): ?float
    {
        return $this->endLat;
    }
    public function endLng(): ?float
    {
        return $this->endLng;
    }

    public function minLat(): ?float
    {
        return $this->minLat;
    }
    public function minLng(): ?float
    {
        return $this->minLng;
    }
    public function maxLat(): ?float
    {
        return $this->maxLat;
    }
    public function maxLng(): ?float
    {
        return $this->maxLng;
    }

    public function polyline(): ?string
    {
        return $this->polyline;
    }  // ✅ NUEVO

    public function distanceM(): int
    {
        return $this->distanceM;
    }
    public function elevationGainM(): int
    {
        return $this->elevationGainM;
    }
    public function elevationLossM(): int
    {
        return $this->elevationLossM;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
