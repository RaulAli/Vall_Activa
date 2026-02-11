<?php
declare(strict_types=1);

namespace App\Application\Offer\Port;

use App\Application\Offer\DTO\OfferPublicDetails;
use App\Application\Shared\DTO\PaginatedResult;

interface OfferPublicReadRepositoryInterface
{
    public function listPublic(int $page, int $limit): PaginatedResult;

    public function findPublicBySlug(string $slug): ?OfferPublicDetails;
}
