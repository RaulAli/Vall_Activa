<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create incident_categories table and seed defaults';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE incident_categories (id VARCHAR(36) NOT NULL, code VARCHAR(30) NOT NULL, name VARCHAR(80) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_incident_category_code ON incident_categories (code)');
        $this->addSql('CREATE INDEX idx_incident_category_active ON incident_categories (is_active)');

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $rows = [
            ['TECHNICAL', 'Problema tecnico'],
            ['PAYMENTS', 'Pagos'],
            ['ACCOUNT', 'Cuenta'],
            ['OTHER', 'Otros'],
        ];

        foreach ($rows as [$code, $name]) {
            $id = Uuid::v4()->value();
            $this->addSql(
                'INSERT INTO incident_categories (id, code, name, is_active, created_at, updated_at) VALUES (:id, :code, :name, true, :createdAt, :updatedAt)',
                [
                    'id' => $id,
                    'code' => $code,
                    'name' => $name,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE incident_categories');
    }
}
