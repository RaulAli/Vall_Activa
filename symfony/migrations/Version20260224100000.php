<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add difficulty column to routes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE routes ADD COLUMN difficulty VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE routes DROP COLUMN IF EXISTS difficulty');
    }
}
