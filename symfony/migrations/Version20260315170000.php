<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace unique guide slot index with non-unique index to allow historical rejected/cancelled bookings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS uniq_guide_slot');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_guide_slot ON guide_route_bookings (guide_user_id, scheduled_for)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_guide_route_bookings_guide_slot');
        $this->addSql('CREATE UNIQUE INDEX uniq_guide_slot ON guide_route_bookings (guide_user_id, scheduled_for)');
    }
}
