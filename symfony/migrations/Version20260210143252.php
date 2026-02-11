<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210143252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route_sources DROP CONSTRAINT fk_route_sources_route');
        $this->addSql('ALTER TABLE route_sources ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE route_sources ALTER route_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE route_sources ALTER uploaded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE route_sources ALTER parsed_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE routes DROP CONSTRAINT fk_routes_sport');
        $this->addSql('ALTER TABLE routes ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER created_by_user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER sport_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER distance_m DROP DEFAULT');
        $this->addSql('ALTER TABLE routes ALTER elevation_gain_m DROP DEFAULT');
        $this->addSql('ALTER TABLE routes ALTER elevation_loss_m DROP DEFAULT');
        $this->addSql('ALTER TABLE routes ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE routes ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE sports ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE sports ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE sports ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER INDEX sports_code_key RENAME TO uniq_sport_code');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route_sources ALTER id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE route_sources ALTER route_id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE route_sources ALTER uploaded_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE route_sources ALTER parsed_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE route_sources ADD CONSTRAINT fk_route_sources_route FOREIGN KEY (route_id) REFERENCES routes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE routes ALTER id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER created_by_user_id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER sport_id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE routes ALTER distance_m SET DEFAULT 0');
        $this->addSql('ALTER TABLE routes ALTER elevation_gain_m SET DEFAULT 0');
        $this->addSql('ALTER TABLE routes ALTER elevation_loss_m SET DEFAULT 0');
        $this->addSql('ALTER TABLE routes ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE routes ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE routes ADD CONSTRAINT fk_routes_sport FOREIGN KEY (sport_id) REFERENCES sports (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sports ALTER id TYPE CHAR(36)');
        $this->addSql('ALTER TABLE sports ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE sports ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER INDEX uniq_sport_code RENAME TO sports_code_key');
    }
}
