<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create guide_route_bookings table for athlete reservations on guide routes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guide_route_bookings (id VARCHAR(36) NOT NULL, route_id VARCHAR(36) NOT NULL, guide_user_id VARCHAR(36) NOT NULL, athlete_user_id VARCHAR(36) NOT NULL, scheduled_for TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_guide_slot ON guide_route_bookings (guide_user_id, scheduled_for)');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_route ON guide_route_bookings (route_id)');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_athlete ON guide_route_bookings (athlete_user_id)');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_status ON guide_route_bookings (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guide_route_bookings');
    }
}
