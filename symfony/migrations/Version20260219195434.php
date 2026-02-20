<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219195434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE refresh_sessions (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, device_id VARCHAR(36) NOT NULL, family_id VARCHAR(36) NOT NULL, current_token_hash VARCHAR(64) NOT NULL, revoked BOOLEAN DEFAULT false NOT NULL, session_version INT DEFAULT 1 NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_rs_user_id ON refresh_sessions (user_id)');
        $this->addSql('CREATE INDEX idx_rs_device_id ON refresh_sessions (device_id)');
        $this->addSql('CREATE INDEX idx_rs_family_id ON refresh_sessions (family_id)');
        $this->addSql('CREATE TABLE refresh_token_blacklist (id VARCHAR(36) NOT NULL, token_hash VARCHAR(64) NOT NULL, user_id VARCHAR(36) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, blacklisted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3D845691B3BC57DA ON refresh_token_blacklist (token_hash)');
        $this->addSql('CREATE INDEX idx_rtb_token_hash ON refresh_token_blacklist (token_hash)');
        $this->addSql('CREATE INDEX idx_rtb_user_id ON refresh_token_blacklist (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE refresh_sessions');
        $this->addSql('DROP TABLE refresh_token_blacklist');
    }
}
