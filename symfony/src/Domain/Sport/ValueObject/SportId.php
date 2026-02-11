<?php
declare(strict_types=1);

namespace App\Domain\Sport\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;

final class SportId
{
    private function __construct(private Uuid $uuid)
    {
    }

    public static function new(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function value(): string
    {
        return $this->uuid->value();
    }
}
