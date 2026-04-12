<?php
declare(strict_types=1);

namespace App\Domain\Incident\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;

final class IncidentId
{
    private function __construct(private Uuid $uuid)
    {
    }

    public static function new(): self
    {
        return new self(Uuid::v4());
    }

    public function value(): string
    {
        return $this->uuid->value();
    }
}
