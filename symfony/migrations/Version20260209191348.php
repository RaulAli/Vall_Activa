<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209191348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offers (id VARCHAR(36) NOT NULL, business_id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, quantity INT DEFAULT 0 NOT NULL, points_cost INT DEFAULT 0 NOT NULL, image VARCHAR(2048) DEFAULT NULL, discount_type VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_offer_business ON offers (business_id)');
        $this->addSql('CREATE INDEX idx_offer_status ON offers (status)');
        $this->addSql('CREATE INDEX idx_offer_active ON offers (is_active)');
        $this->addSql('CREATE UNIQUE INDEX uniq_offer_slug_business ON offers (business_id, slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE offers');
    }
}
