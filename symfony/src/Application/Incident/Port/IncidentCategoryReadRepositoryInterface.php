<?php
declare(strict_types=1);

namespace App\Application\Incident\Port;

interface IncidentCategoryReadRepositoryInterface
{
    public function isActiveCode(string $code): bool;

    /**
     * @return list<array{code: string, name: string}>
     */
    public function listActive(): array;
}
