<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216191119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business_profiles ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE business_profiles ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE business_profiles ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE business_profiles ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE business_profiles ALTER updated_at DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83008BA6989D9B62 ON business_profiles (slug)');
        $this->addSql('ALTER TABLE routes ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_83008BA6989D9B62');
        $this->addSql('ALTER TABLE business_profiles DROP slug');
        $this->addSql('ALTER TABLE business_profiles ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE business_profiles ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE business_profiles ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE business_profiles ALTER updated_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE routes DROP image');
    }
}
