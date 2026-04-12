<?php
declare(strict_types=1);

namespace App\Application\Incident\Handler;

use App\Application\Incident\Command\CreateIncidentCommand;
use App\Application\Incident\Port\IncidentCategoryReadRepositoryInterface;
use App\Application\Shared\Port\TransactionInterface;
use App\Domain\Incident\Entity\Incident;
use App\Domain\Incident\Repository\IncidentRepositoryInterface;

final class CreateIncidentHandler
{
    public function __construct(
        private readonly TransactionInterface $tx,
        private readonly IncidentRepositoryInterface $incidents,
        private readonly IncidentCategoryReadRepositoryInterface $categories,
    ) {
    }

    public function __invoke(CreateIncidentCommand $cmd): string
    {
        if (!$this->categories->isActiveCode($cmd->category)) {
            throw new \InvalidArgumentException('Invalid or inactive category.');
        }

        return $this->tx->run(function () use ($cmd): string {
            $incident = Incident::create(
                userId: $cmd->actor->userId->value(),
                category: $cmd->category,
                subject: $cmd->subject,
                message: $cmd->message,
            );

            $this->incidents->save($incident);

            return $incident->id()->value();
        });
    }
}
