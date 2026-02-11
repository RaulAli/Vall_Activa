<?php
declare(strict_types=1);

namespace App\Domain\Identity\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;

final class UserId
{
    private function __construct(private Uuid $uuid) {}

    public static function new(): self { return new self(Uuid::v4()); }
    public static function fromString(string $id): self { return new self(Uuid::fromString($id)); }

    public function value(): string { return $this->uuid->value(); }
}
