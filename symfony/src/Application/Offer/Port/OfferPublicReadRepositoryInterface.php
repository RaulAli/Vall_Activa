<?php
declare(strict_types=1);

namespace App\Application\Offer\Port;

use App\Application\Offer\DTO\BusinessMapMarker;
use App\Application\Offer\DTO\OfferFiltersMeta;
use App\Application\Offer\DTO\OfferPublicDetails;
use App\Application\Offer\DTO\OfferPublicFilters;
use App\Application\Shared\DTO\PaginatedResult;

interface OfferPublicReadRepositoryInterface
{
    public function listPublic(OfferPublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult;

    /** @return BusinessMapMarker[] */
    public function listMapBusinesses(OfferPublicFilters $filters): array;

    public function findPublicBySlug(string $slug): ?OfferPublicDetails;

    public function getFiltersMeta(OfferPublicFilters $filters): OfferFiltersMeta;
}
