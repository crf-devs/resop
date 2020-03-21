<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200321221102 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE commissionable_asset_availability_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE commissionable_asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE commissionable_asset_availability (id INT NOT NULL, asset_id INT NOT NULL, planning_agent_id INT DEFAULT NULL, start_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, end_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, booked_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_202CCBE15DA1941 ON commissionable_asset_availability (asset_id)');
        $this->addSql('CREATE INDEX IDX_202CCBE131127A6 ON commissionable_asset_availability (planning_agent_id)');
        $this->addSql('CREATE UNIQUE INDEX asset_availability_slot_unique ON commissionable_asset_availability (asset_id, start_time, end_time)');
        $this->addSql('COMMENT ON COLUMN commissionable_asset_availability.start_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN commissionable_asset_availability.end_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN commissionable_asset_availability.booked_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN commissionable_asset_availability.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN commissionable_asset_availability.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE commissionable_asset (id INT NOT NULL, organization_id INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, last_commission_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4D68E55532C8A3DE ON commissionable_asset (organization_id)');
        $this->addSql('COMMENT ON COLUMN commissionable_asset.last_commission_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE commissionable_asset_availability ADD CONSTRAINT FK_202CCBE15DA1941 FOREIGN KEY (asset_id) REFERENCES commissionable_asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commissionable_asset_availability ADD CONSTRAINT FK_202CCBE131127A6 FOREIGN KEY (planning_agent_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commissionable_asset ADD CONSTRAINT FK_4D68E55532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commissionable_asset_availability DROP CONSTRAINT FK_202CCBE15DA1941');
        $this->addSql('DROP SEQUENCE commissionable_asset_availability_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE commissionable_asset_id_seq CASCADE');
        $this->addSql('DROP TABLE commissionable_asset_availability');
        $this->addSql('DROP TABLE commissionable_asset');
    }
}
