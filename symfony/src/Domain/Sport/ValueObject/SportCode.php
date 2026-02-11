<?php
declare(strict_types=1);

namespace App\Domain\Sport\ValueObject;

final class SportCode
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $v = strtolower(trim($value));
        if ($v === '' || strlen($v) > 50) {
            throw new \InvalidArgumentException('Invalid sport code.');
        }
        // opcional: permitir solo a-z0-9_-:
        if (!preg_match('/^[a-z0-9_-]+$/', $v)) {
            throw new \InvalidArgumentException('Invalid sport code format.');
        }
        return new self($v);
    }

    public function value(): string
    {
        return $this->value;
    }
}
