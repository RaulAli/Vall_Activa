<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add previous_token_hash and rotated_at to refresh_sessions for idempotent rotation grace period';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_sessions ADD COLUMN previous_token_hash VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE refresh_sessions ADD COLUMN rotated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_rs_previous_token_hash ON refresh_sessions (previous_token_hash)');
        $this->addSql('COMMENT ON COLUMN refresh_sessions.rotated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_rs_previous_token_hash');
        $this->addSql('ALTER TABLE refresh_sessions DROP COLUMN previous_token_hash');
        $this->addSql('ALTER TABLE refresh_sessions DROP COLUMN rotated_at');
    }
}
