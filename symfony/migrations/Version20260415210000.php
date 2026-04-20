<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add offer redemptions for athlete point-based offer usage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE offer_redemptions (id VARCHAR(36) NOT NULL, offer_id VARCHAR(36) NOT NULL, athlete_user_id VARCHAR(36) NOT NULL, points_spent INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_offer_redemptions_offer ON offer_redemptions (offer_id)');
        $this->addSql('CREATE INDEX idx_offer_redemptions_athlete ON offer_redemptions (athlete_user_id)');
        $this->addSql('ALTER TABLE offer_redemptions ADD CONSTRAINT chk_offer_redemptions_points_spent CHECK (points_spent > 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offer_redemptions DROP CONSTRAINT chk_offer_redemptions_points_spent');
        $this->addSql('DROP TABLE offer_redemptions');
    }
}
