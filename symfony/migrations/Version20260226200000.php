<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set default robohash avatar for existing users without an avatar';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE athlete_profiles   SET avatar        = CONCAT('https://robohash.org/', slug, '.png') WHERE avatar        IS NULL");
        $this->addSql("UPDATE guide_profiles     SET avatar        = CONCAT('https://robohash.org/', slug, '.png') WHERE avatar        IS NULL");
        $this->addSql("UPDATE business_profiles  SET profile_icon  = CONCAT('https://robohash.org/', slug, '.png') WHERE profile_icon  IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE athlete_profiles   SET avatar        = NULL WHERE avatar       LIKE 'https://robohash.org/%'");
        $this->addSql("UPDATE guide_profiles     SET avatar        = NULL WHERE avatar       LIKE 'https://robohash.org/%'");
        $this->addSql("UPDATE business_profiles  SET profile_icon  = NULL WHERE profile_icon LIKE 'https://robohash.org/%'");
    }
}
