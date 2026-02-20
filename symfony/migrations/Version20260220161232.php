<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220161232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE athlete_profiles (user_id VARCHAR(36) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, avatar VARCHAR(2048) DEFAULT NULL, birth_date DATE DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_56423B95989D9B62 ON athlete_profiles (slug)');
        $this->addSql('CREATE INDEX idx_athlete_profiles_slug ON athlete_profiles (slug)');
        $this->addSql('CREATE INDEX idx_athlete_profiles_active ON athlete_profiles (is_active)');
        $this->addSql('CREATE TABLE guide_profiles (user_id VARCHAR(36) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, avatar VARCHAR(2048) DEFAULT NULL, bio TEXT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, lat DOUBLE PRECISION DEFAULT NULL, lng DOUBLE PRECISION DEFAULT NULL, sports JSON NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BEB13B50989D9B62 ON guide_profiles (slug)');
        $this->addSql('CREATE INDEX idx_guide_profiles_slug ON guide_profiles (slug)');
        $this->addSql('CREATE INDEX idx_guide_profiles_active ON guide_profiles (is_active)');
        $this->addSql('CREATE INDEX idx_guide_profiles_lat_lng ON guide_profiles (lat, lng)');
        $this->addSql('DROP INDEX uniq_1483a5e9989d9b62');
        $this->addSql('ALTER TABLE users DROP slug');
        $this->addSql('ALTER TABLE users DROP name');
        $this->addSql('ALTER TABLE users DROP avatar');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE athlete_profiles');
        $this->addSql('DROP TABLE guide_profiles');
        $this->addSql('ALTER TABLE users ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD avatar VARCHAR(512) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_1483a5e9989d9b62 ON users (slug)');
    }
}
