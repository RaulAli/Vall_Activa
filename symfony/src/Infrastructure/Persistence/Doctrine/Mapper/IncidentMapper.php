<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Incident\Entity\Incident;
use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentOrm;

final class IncidentMapper
{
    public function toOrm(Incident $incident): IncidentOrm
    {
        $orm = new IncidentOrm();

        $orm->id = $incident->id()->value();
        $orm->userId = $incident->userId();
        $orm->category = $incident->category();
        $orm->subject = $incident->subject();
        $orm->message = $incident->message();
        $orm->status = $incident->status()->value;

        $now = new \DateTimeImmutable();
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        return $orm;
    }
}
