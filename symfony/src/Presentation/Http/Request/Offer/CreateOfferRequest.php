<?php
declare(strict_types=1);

namespace App\Presentation\Http\Request\Offer;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOfferRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,

        #[Assert\NotBlank]
        public string $slug,

        public ?string $description = null,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/')]
        public string $price = '0.00',

        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 3)]
        public string $currency = 'EUR',

        public bool $isActive = true,

        #[Assert\PositiveOrZero]
        public int $quantity = 0,

        #[Assert\PositiveOrZero]
        public int $pointsCost = 0,

        #[Assert\Url]
        public ?string $image = null,

        #[Assert\Choice(choices: ['AMOUNT', 'PERCENT', 'NONE'])]
        public string $discountType = 'NONE',

        #[Assert\Choice(choices: ['DRAFT', 'PUBLISHED', 'ARCHIVED'])]
        public string $status = 'DRAFT'
    ) {
    }
}

