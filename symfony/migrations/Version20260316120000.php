<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ends_at to guide_route_bookings for multi-slot range booking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guide_route_bookings ADD ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("UPDATE guide_route_bookings SET ends_at = scheduled_for + INTERVAL '60 minutes'");
        $this->addSql('ALTER TABLE guide_route_bookings ALTER COLUMN ends_at SET NOT NULL');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_ends_at ON guide_route_bookings (ends_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_guide_route_bookings_ends_at');
        $this->addSql('ALTER TABLE guide_route_bookings DROP ends_at');
    }
}
