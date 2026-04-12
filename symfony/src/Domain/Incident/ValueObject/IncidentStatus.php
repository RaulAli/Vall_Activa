<?php
declare(strict_types=1);

namespace App\Domain\Incident\ValueObject;

enum IncidentStatus: string
{
    case OPEN = 'OPEN';
    case REVIEWING = 'REVIEWING';
    case RESOLVED = 'RESOLVED';
}
