<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add VIP subscription fields to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD vip_active BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE users ADD vip_plan VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD vip_started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD vip_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_users_vip_active ON users (vip_active)');
        $this->addSql("ALTER TABLE users ADD CONSTRAINT chk_users_vip_plan CHECK (vip_plan IS NULL OR vip_plan IN ('MONTHLY', 'YEARLY'))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP CONSTRAINT chk_users_vip_plan');
        $this->addSql('DROP INDEX idx_users_vip_active');
        $this->addSql('ALTER TABLE users DROP vip_expires_at');
        $this->addSql('ALTER TABLE users DROP vip_started_at');
        $this->addSql('ALTER TABLE users DROP vip_plan');
        $this->addSql('ALTER TABLE users DROP vip_active');
    }
}
