<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415174500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vip_cancel_at_period_end flag to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD vip_cancel_at_period_end BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP vip_cancel_at_period_end');
    }
}
