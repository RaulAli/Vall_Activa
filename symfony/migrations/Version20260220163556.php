<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220163556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Add nullable first so existing rows don't violate NOT NULL
        $this->addSql('ALTER TABLE users ADD role VARCHAR(50) DEFAULT NULL');

        // 2. Migrate data: extract first element of the JSON roles array
        $this->addSql("UPDATE users SET role = roles->>0 WHERE roles IS NOT NULL AND jsonb_array_length(roles::jsonb) > 0");

        // 3. Fallback: anyone still null gets ROLE_BUSINESS
        $this->addSql("UPDATE users SET role = 'ROLE_BUSINESS' WHERE role IS NULL");

        // 4. Now enforce NOT NULL
        $this->addSql('ALTER TABLE users ALTER COLUMN role SET NOT NULL');
        $this->addSql('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');

        // 5. Drop old column
        $this->addSql('ALTER TABLE users DROP roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD roles JSON DEFAULT NULL');
        $this->addSql("UPDATE users SET roles = json_build_array(role)");
        $this->addSql('ALTER TABLE users ALTER COLUMN roles SET NOT NULL');
        $this->addSql('ALTER TABLE users DROP role');
    }
}
