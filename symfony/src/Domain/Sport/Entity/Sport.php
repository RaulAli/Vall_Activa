<?php
declare(strict_types=1);

namespace App\Domain\Sport\Entity;

use App\Domain\Sport\ValueObject\SportCode;
use App\Domain\Sport\ValueObject\SportId;

final class Sport
{
    private function __construct(
        private SportId $id,
        private SportCode $code,
        private string $name,
        private bool $isActive
    ) {
    }

    public static function create(SportCode $code, string $name): self
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Sport name cannot be empty.');
        }
        return new self(SportId::new(), $code, $name, true);
    }

    public function id(): SportId
    {
        return $this->id;
    }
    public function code(): SportCode
    {
        return $this->code;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }

    public static function fromPersistence(
        \App\Domain\Sport\ValueObject\SportId $id,
        \App\Domain\Sport\ValueObject\SportCode $code,
        string $name,
        bool $isActive
    ): self {
        return new self($id, $code, $name, $isActive);
    }

}
