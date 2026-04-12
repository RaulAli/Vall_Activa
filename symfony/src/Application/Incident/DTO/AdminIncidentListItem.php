<?php
declare(strict_types=1);

namespace App\Application\Incident\DTO;

final class AdminIncidentListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly ?string $userEmail,
        public readonly string $category,
        public readonly string $subject,
        public readonly string $message,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }
}
