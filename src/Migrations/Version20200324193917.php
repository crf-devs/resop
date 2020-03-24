<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200324193917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing asset index';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commissionable_asset DROP last_commission_date');
        $this->addSql('CREATE INDEX commissionable_asset_type_idx ON commissionable_asset (type)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX commissionable_asset_type_idx');
        $this->addSql('ALTER TABLE commissionable_asset ADD last_commission_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN commissionable_asset.last_commission_date IS \'(DC2Type:datetimetz_immutable)\'');
    }
}
