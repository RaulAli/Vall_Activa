<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create incidents table for user support reports';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE incidents (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, category VARCHAR(50) NOT NULL, subject VARCHAR(120) NOT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_incident_user ON incidents (user_id)');
        $this->addSql('CREATE INDEX idx_incident_status ON incidents (status)');
        $this->addSql('CREATE INDEX idx_incident_created_at ON incidents (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE incidents');
    }
}
