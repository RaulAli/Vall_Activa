<?php
declare(strict_types=1);

namespace App\Application\Incident\Handler;

use App\Application\Incident\Port\AdminIncidentReadRepositoryInterface;
use App\Application\Incident\Query\ListAdminIncidentsQuery;

final class ListAdminIncidentsHandler
{
    public function __construct(private readonly AdminIncidentReadRepositoryInterface $repo)
    {
    }

    public function __invoke(ListAdminIncidentsQuery $query): array
    {
        $q = $query->q !== null ? trim($query->q) : null;
        $q = $q !== '' ? $q : null;

        $status = $query->status !== null ? strtoupper(trim($query->status)) : null;
        $status = in_array($status, ['OPEN', 'REVIEWING', 'RESOLVED'], true) ? $status : null;

        return $this->repo->list($q, $status);
    }
}
