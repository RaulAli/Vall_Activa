<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add payment fields to guide_route_bookings for Stripe checkout';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE guide_route_bookings ADD payment_status VARCHAR(20) NOT NULL DEFAULT 'UNPAID'");
        $this->addSql('ALTER TABLE guide_route_bookings ADD payment_amount_cents INT NOT NULL DEFAULT 2500');
        $this->addSql("ALTER TABLE guide_route_bookings ADD payment_currency VARCHAR(3) NOT NULL DEFAULT 'EUR'");
        $this->addSql('ALTER TABLE guide_route_bookings ADD stripe_checkout_session_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE guide_route_bookings ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE guide_route_bookings ADD paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_payment_status ON guide_route_bookings (payment_status)');
        $this->addSql('CREATE INDEX idx_guide_route_bookings_checkout_session ON guide_route_bookings (stripe_checkout_session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_guide_route_bookings_checkout_session');
        $this->addSql('DROP INDEX idx_guide_route_bookings_payment_status');
        $this->addSql('ALTER TABLE guide_route_bookings DROP paid_at');
        $this->addSql('ALTER TABLE guide_route_bookings DROP stripe_payment_intent_id');
        $this->addSql('ALTER TABLE guide_route_bookings DROP stripe_checkout_session_id');
        $this->addSql('ALTER TABLE guide_route_bookings DROP payment_currency');
        $this->addSql('ALTER TABLE guide_route_bookings DROP payment_amount_cents');
        $this->addSql('ALTER TABLE guide_route_bookings DROP payment_status');
    }
}
