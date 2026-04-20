<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add points engine tables, mission system and points balance for users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD points_balance INT DEFAULT 0 NOT NULL');

        $this->addSql('CREATE TABLE point_settings (id VARCHAR(36) NOT NULL, points_per_km INT DEFAULT 50 NOT NULL, daily_cap_athlete INT DEFAULT 1000 NOT NULL, daily_cap_vip INT DEFAULT 1500 NOT NULL, vip_multiplier INT DEFAULT 2 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE point_missions (id VARCHAR(36) NOT NULL, code VARCHAR(80) NOT NULL, title VARCHAR(120) NOT NULL, description TEXT DEFAULT NULL, points_reward INT DEFAULT 100 NOT NULL, is_active BOOLEAN DEFAULT TRUE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_point_mission_code ON point_missions (code)');
        $this->addSql('CREATE INDEX idx_point_mission_active ON point_missions (is_active)');

        $this->addSql('CREATE TABLE points_ledger (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, source_type VARCHAR(30) NOT NULL, source_ref VARCHAR(120) NOT NULL, base_points INT NOT NULL, multiplier INT DEFAULT 1 NOT NULL, awarded_points INT NOT NULL, day_key DATE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_points_ledger_source ON points_ledger (user_id, source_type, source_ref)');
        $this->addSql('CREATE INDEX idx_points_ledger_user_day ON points_ledger (user_id, day_key)');

        $this->addSql('CREATE TABLE point_mission_completions (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, mission_id VARCHAR(36) NOT NULL, day_key DATE NOT NULL, awarded_points INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_point_mission_completion_day ON point_mission_completions (user_id, mission_id, day_key)');
        $this->addSql('CREATE INDEX idx_point_mission_completion_user_day ON point_mission_completions (user_id, day_key)');

        $this->addSql('ALTER TABLE point_settings ADD CONSTRAINT chk_point_settings_points_per_km CHECK (points_per_km > 0)');
        $this->addSql('ALTER TABLE point_settings ADD CONSTRAINT chk_point_settings_caps CHECK (daily_cap_athlete > 0 AND daily_cap_vip > 0 AND daily_cap_vip >= daily_cap_athlete)');
        $this->addSql('ALTER TABLE point_settings ADD CONSTRAINT chk_point_settings_multiplier CHECK (vip_multiplier >= 1)');
        $this->addSql('ALTER TABLE point_missions ADD CONSTRAINT chk_point_missions_points_reward CHECK (points_reward > 0)');
        $this->addSql('ALTER TABLE points_ledger ADD CONSTRAINT chk_points_ledger_non_negative CHECK (base_points >= 0 AND awarded_points >= 0 AND multiplier >= 1)');
        $this->addSql('ALTER TABLE point_mission_completions ADD CONSTRAINT chk_point_mission_completions_awarded_points CHECK (awarded_points >= 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE point_mission_completions DROP CONSTRAINT chk_point_mission_completions_awarded_points');
        $this->addSql('ALTER TABLE points_ledger DROP CONSTRAINT chk_points_ledger_non_negative');
        $this->addSql('ALTER TABLE point_missions DROP CONSTRAINT chk_point_missions_points_reward');
        $this->addSql('ALTER TABLE point_settings DROP CONSTRAINT chk_point_settings_multiplier');
        $this->addSql('ALTER TABLE point_settings DROP CONSTRAINT chk_point_settings_caps');
        $this->addSql('ALTER TABLE point_settings DROP CONSTRAINT chk_point_settings_points_per_km');

        $this->addSql('DROP TABLE point_mission_completions');
        $this->addSql('DROP TABLE points_ledger');
        $this->addSql('DROP TABLE point_missions');
        $this->addSql('DROP TABLE point_settings');

        $this->addSql('ALTER TABLE users DROP points_balance');
    }
}
