<?php
declare(strict_types=1);

namespace App\Application\Incident\Command;

use App\Application\Shared\Security\Actor;

final class CreateIncidentCommand
{
    public function __construct(
        public readonly Actor $actor,
        public readonly string $category,
        public readonly string $subject,
        public readonly string $message,
    ) {
    }
}
