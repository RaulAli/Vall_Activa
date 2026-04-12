<?php
declare(strict_types=1);

namespace App\Application\Incident\Query;

final class ListAdminIncidentsQuery
{
    public function __construct(
        public readonly ?string $q,
        public readonly ?string $status,
    ) {
    }
}
