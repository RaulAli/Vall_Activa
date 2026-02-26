<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin_disabled column to routes and offers tables for admin soft-deactivation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes ADD COLUMN admin_disabled BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE offers ADD COLUMN admin_disabled BOOLEAN NOT NULL DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes DROP COLUMN admin_disabled');
        $this->addSql('ALTER TABLE offers DROP COLUMN admin_disabled');
    }
}
