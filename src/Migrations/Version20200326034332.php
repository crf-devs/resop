<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200326034332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Vehicle attributes';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commissionable_asset ADD has_mobile_radio BOOLEAN DEFAULT FALSE');
        $this->addSql('ALTER TABLE commissionable_asset ALTER COLUMN has_mobile_radio DROP DEFAULT');

        $this->addSql('ALTER TABLE commissionable_asset ADD has_first_aid_kit BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE commissionable_asset ALTER COLUMN has_first_aid_kit DROP DEFAULT ');

        $this->addSql('ALTER TABLE commissionable_asset ADD parking_location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commissionable_asset ADD contact VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE commissionable_asset ADD seating_capacity INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE commissionable_asset ALTER COLUMN seating_capacity DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commissionable_asset DROP has_mobile_radio');
        $this->addSql('ALTER TABLE commissionable_asset DROP has_first_aid_kit');
        $this->addSql('ALTER TABLE commissionable_asset DROP parking_location');
        $this->addSql('ALTER TABLE commissionable_asset DROP contact');
        $this->addSql('ALTER TABLE commissionable_asset DROP seating_capacity');
    }
}
