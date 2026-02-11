<?php
declare(strict_types=1);

namespace App\Application\Shared\DTO;

final class PaginatedResult
{
    /**
     * @param array<int, mixed> $items
     */
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total,
        public readonly array $items
    ) {
    }
}
