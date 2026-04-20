<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add guide hourly rate in cents to guide_profiles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guide_profiles ADD price_per_hour_cents INT NOT NULL DEFAULT 2500');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guide_profiles DROP price_per_hour_cents');
    }
}
