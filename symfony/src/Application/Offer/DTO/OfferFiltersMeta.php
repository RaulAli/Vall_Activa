<?php
declare(strict_types=1);

namespace App\Application\Offer\DTO;

final class OfferFiltersMeta
{
    /**
     * discountTypes: array<array{value:string,count:int}>
     */
    public function __construct(
        public readonly array $price,   // {min:?string,max:?string}
        public readonly array $points,  // {min:?int,max:?int}
        public readonly array $discountTypes,
        public readonly array $counts,  // {offers:int,businesses:int}
        public readonly ?array $bounds  // {minLng:float,minLat:float,maxLng:float,maxLat:float} | null
    ) {
    }
}
