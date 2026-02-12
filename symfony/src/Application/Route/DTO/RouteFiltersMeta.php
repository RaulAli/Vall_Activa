<?php
declare(strict_types=1);

namespace App\Application\Route\DTO;

final class RouteFiltersMeta
{
    /**
     * sports: array<array{code:string,name:string,count:int}>
     */
    public function __construct(
        public readonly array $distance, // {min:?int,max:?int}
        public readonly array $gain,     // {min:?int,max:?int}
        public readonly array $sports,
        public readonly array $counts,   // {routes:int}
        public readonly ?array $bounds   // {minLng:float,minLat:float,maxLng:float,maxLat:float} | null
    ) {
    }
}
