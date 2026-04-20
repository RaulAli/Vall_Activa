<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default automatic daily missions (10km and first route upload)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO point_missions (id, code, title, description, points_reward, is_active, created_at, updated_at)
            SELECT '8f2d12f6-6db7-4f32-99fd-bde173f44b01', 'DAILY_10KM', 'Completa 10 km hoy', 'Completa 10 km acumulados en reservas pagadas y completadas en el dia.', 300, true, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM point_missions WHERE code = 'DAILY_10KM')");

        $this->addSql("INSERT INTO point_missions (id, code, title, description, points_reward, is_active, created_at, updated_at)
            SELECT 'dc94137c-c728-4ba6-9faa-3ca6f8b32511', 'FIRST_ROUTE_UPLOAD_DAILY', 'Sube tu primera ruta del dia', 'Publica tu primera ruta del dia para obtener puntos extra.', 200, true, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM point_missions WHERE code = 'FIRST_ROUTE_UPLOAD_DAILY')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM point_missions WHERE code IN ('DAILY_10KM', 'FIRST_ROUTE_UPLOAD_DAILY')");
    }
}
