<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add duration_seconds column to routes table (extracted from GPX timestamps)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes ADD COLUMN duration_seconds INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes DROP COLUMN duration_seconds');
    }
}
