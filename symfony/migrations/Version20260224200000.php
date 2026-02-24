<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add route_type column to routes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE routes ADD COLUMN route_type VARCHAR(20) DEFAULT NULL");
        $this->addSql("ALTER TABLE routes ADD CONSTRAINT chk_route_type CHECK (route_type IS NULL OR route_type IN ('CIRCULAR','LINEAR','ROUND_TRIP'))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes DROP CONSTRAINT IF EXISTS chk_route_type');
        $this->addSql('ALTER TABLE routes DROP COLUMN IF EXISTS route_type');
    }
}
