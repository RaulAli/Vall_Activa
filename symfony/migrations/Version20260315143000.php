<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_verified flag to guide_profiles for admin verification workflow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guide_profiles ADD COLUMN is_verified BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('CREATE INDEX idx_guide_profiles_verified ON guide_profiles (is_verified)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_guide_profiles_verified');
        $this->addSql('ALTER TABLE guide_profiles DROP COLUMN is_verified');
    }
}
