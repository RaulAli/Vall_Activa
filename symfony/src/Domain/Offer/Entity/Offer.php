<?php
declare(strict_types=1);

namespace App\Domain\Offer\Entity;

use App\Domain\Offer\ValueObject\DiscountType;
use App\Domain\Offer\ValueObject\OfferId;
use App\Domain\Offer\ValueObject\OfferStatus;

final class Offer
{
    private function __construct(
        private OfferId $id,
        private string $businessId,

        private string $title,
        private string $slug,
        private ?string $description,

        private string $price,      // decimal como string (10.00)
        private string $currency,   // "EUR"

        private bool $isActive,
        private int $quantity,
        private int $pointsCost,

        private ?string $image,

        private DiscountType $discountType,
        private OfferStatus $status
    ) {}

    public static function create(
        string $businessId,
        string $title,
        string $slug,
        ?string $description,
        string $price,
        string $currency,
        bool $isActive,
        int $quantity,
        int $pointsCost,
        ?string $image,
        DiscountType $discountType,
        OfferStatus $status
    ): self {
        // Invariantes mínimas (puedes endurecer después)
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }
        if ($pointsCost < 0) {
            throw new \InvalidArgumentException('Points cost cannot be negative.');
        }
        if (strlen($currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be ISO-4217 (3 letters).');
        }

        return new self(
            OfferId::new(),
            $businessId,
            $title,
            $slug,
            $description,
            $price,
            strtoupper($currency),
            $isActive,
            $quantity,
            $pointsCost,
            $image,
            $discountType,
            $status
        );
    }

    public function id(): OfferId { return $this->id; }
    public function businessId(): string { return $this->businessId; }

    public function title(): string { return $this->title; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }

    public function price(): string { return $this->price; }
    public function currency(): string { return $this->currency; }

    public function isActive(): bool { return $this->isActive; }
    public function quantity(): int { return $this->quantity; }
    public function pointsCost(): int { return $this->pointsCost; }

    public function image(): ?string { return $this->image; }

    public function discountType(): DiscountType { return $this->discountType; }
    public function status(): OfferStatus { return $this->status; }
}
