<?php
declare(strict_types=1);

namespace App\Application\Incident\Port;

use App\Application\Incident\DTO\AdminIncidentListItem;

interface AdminIncidentReadRepositoryInterface
{
    /** @return list<AdminIncidentListItem> */
    public function list(?string $q, ?string $status): array;
}
