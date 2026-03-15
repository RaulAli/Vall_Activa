<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create guide_availabilities table for weekly guide booking slots';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guide_availabilities (user_id VARCHAR(36) NOT NULL, timezone VARCHAR(64) NOT NULL, slot_minutes INT NOT NULL DEFAULT 60, week JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(user_id))');
        $this->addSql('CREATE INDEX idx_guide_availabilities_updated_at ON guide_availabilities (updated_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guide_availabilities');
    }
}
