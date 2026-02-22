<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed / normalise sports: rename CYC→BIKE, add HIKE, SKI, CLIMB, KAYAK, SURF, SWIM';
    }

    public function up(Schema $schema): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        // Rename CYC → BIKE (keep same UUID)
        $this->addSql("UPDATE sports SET code = 'BIKE', name = 'Cycling' WHERE code = 'CYC'");

        // Insert missing sports (INSERT ... ON CONFLICT DO NOTHING is safe for reruns)
        $sports = [
            ['e1a2b3c4-d5e6-f7a8-b9c0-d1e2f3a4b5c6', 'HIKE',  'Hiking'],
            ['f2b3c4d5-e6f7-a8b9-c0d1-e2f3a4b5c6d7', 'SKI',   'Skiing'],
            ['a3c4d5e6-f7a8-b9c0-d1e2-f3a4b5c6d7e8', 'CLIMB', 'Climbing'],
            ['b4d5e6f7-a8b9-c0d1-e2f3-a4b5c6d7e8f9', 'KAYAK', 'Kayaking'],
            ['c5e6f7a8-b9c0-d1e2-f3a4-b5c6d7e8f9a0', 'SURF',  'Surfing'],
            ['d6f7a8b9-c0d1-e2f3-a4b5-c6d7e8f9a0b1', 'SWIM',  'Swimming'],
        ];

        foreach ($sports as [$id, $code, $name]) {
            $this->addSql(
                "INSERT INTO sports (id, code, name, is_active, created_at, updated_at)
                 VALUES ('$id', '$code', '$name', TRUE, '$now', '$now')
                 ON CONFLICT (code) DO NOTHING"
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE sports SET code = 'CYC', name = 'Cycling' WHERE code = 'BIKE'");
        $this->addSql("DELETE FROM sports WHERE code IN ('HIKE','SKI','CLIMB','KAYAK','SURF','SWIM')");
    }
}
