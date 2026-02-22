<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix sport UUIDs that do not conform to the UUID v4 format required by
 * Uuid::fromString() (version nibble must be 1-5, variant nibble must be 8-b).
 * BIKE and RUN were already valid; CLIMB, HIKE, KAYAK, SKI, SURF, SWIM are fixed here.
 */
final class Version20260223200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix invalid sport UUIDs to proper UUID v4 format';
    }

    public function up(Schema $schema): void
    {
        // Map old → new UUID for each sport that had an invalid UUID
        $fixes = [
            'CLIMB' => ['old' => 'a3c4d5e6-f7a8-b9c0-d1e2-f3a4b5c6d7e8', 'new' => 'a3c4d5e6-f7a8-49c0-b1e2-f3a4b5c6d7e8'],
            'HIKE'  => ['old' => 'e1a2b3c4-d5e6-f7a8-b9c0-d1e2f3a4b5c6', 'new' => 'e1a2b3c4-d5e6-47a8-b9c0-d1e2f3a4b5c6'],
            'KAYAK' => ['old' => 'b4d5e6f7-a8b9-c0d1-e2f3-a4b5c6d7e8f9', 'new' => 'b4d5e6f7-a8b9-40d1-92f3-a4b5c6d7e8f9'],
            'SKI'   => ['old' => 'f2b3c4d5-e6f7-a8b9-c0d1-e2f3a4b5c6d7', 'new' => 'f2b3c4d5-e6f7-48b9-80d1-e2f3a4b5c6d7'],
            'SURF'  => ['old' => 'c5e6f7a8-b9c0-d1e2-f3a4-b5c6d7e8f9a0', 'new' => 'c5e6f7a8-b9c0-41e2-a3a4-b5c6d7e8f9a0'],
            'SWIM'  => ['old' => 'd6f7a8b9-c0d1-e2f3-a4b5-c6d7e8f9a0b1', 'new' => 'd6f7a8b9-c0d1-42f3-a4b5-c6d7e8f9a0b1'],
        ];

        foreach ($fixes as $code => $ids) {
            // Update routes that reference this sport_id first (FK safety)
            $this->addSql(
                'UPDATE routes SET sport_id = :new WHERE sport_id = :old',
                ['new' => $ids['new'], 'old' => $ids['old']]
            );
            // Then update the sport itself
            $this->addSql(
                'UPDATE sports SET id = :new WHERE code = :code AND id = :old',
                ['new' => $ids['new'], 'code' => $code, 'old' => $ids['old']]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $fixes = [
            'CLIMB' => ['old' => 'a3c4d5e6-f7a8-b9c0-d1e2-f3a4b5c6d7e8', 'new' => 'a3c4d5e6-f7a8-49c0-b1e2-f3a4b5c6d7e8'],
            'HIKE'  => ['old' => 'e1a2b3c4-d5e6-f7a8-b9c0-d1e2f3a4b5c6', 'new' => 'e1a2b3c4-d5e6-47a8-b9c0-d1e2f3a4b5c6'],
            'KAYAK' => ['old' => 'b4d5e6f7-a8b9-c0d1-e2f3-a4b5c6d7e8f9', 'new' => 'b4d5e6f7-a8b9-40d1-92f3-a4b5c6d7e8f9'],
            'SKI'   => ['old' => 'f2b3c4d5-e6f7-a8b9-c0d1-e2f3a4b5c6d7', 'new' => 'f2b3c4d5-e6f7-48b9-80d1-e2f3a4b5c6d7'],
            'SURF'  => ['old' => 'c5e6f7a8-b9c0-d1e2-f3a4-b5c6d7e8f9a0', 'new' => 'c5e6f7a8-b9c0-41e2-a3a4-b5c6d7e8f9a0'],
            'SWIM'  => ['old' => 'd6f7a8b9-c0d1-e2f3-a4b5-c6d7e8f9a0b1', 'new' => 'd6f7a8b9-c0d1-42f3-a4b5-c6d7e8f9a0b1'],
        ];

        foreach ($fixes as $code => $ids) {
            $this->addSql(
                'UPDATE routes SET sport_id = :old WHERE sport_id = :new',
                ['new' => $ids['new'], 'old' => $ids['old']]
            );
            $this->addSql(
                'UPDATE sports SET id = :old WHERE code = :code AND id = :new',
                ['new' => $ids['new'], 'code' => $code, 'old' => $ids['old']]
            );
        }
    }
}
