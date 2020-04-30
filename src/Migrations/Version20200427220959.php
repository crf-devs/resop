<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200427220959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missions';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE mission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mission (id INT NOT NULL, organization_id INT DEFAULT NULL, type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, start_time TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, end_time TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9067F23C32C8A3DE ON mission (organization_id)');
        $this->addSql('CREATE INDEX IDX_9067F23CC54C8C93 ON mission (type_id)');
        $this->addSql('CREATE INDEX mission_start_end_idx ON mission (start_time, end_time)');
        $this->addSql('COMMENT ON COLUMN mission.start_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mission.end_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE mission_user (mission_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(mission_id, user_id))');
        $this->addSql('CREATE INDEX IDX_A4D17A46BE6CAE90 ON mission_user (mission_id)');
        $this->addSql('CREATE INDEX IDX_A4D17A46A76ED395 ON mission_user (user_id)');
        $this->addSql('CREATE TABLE mission_commissionable_asset (mission_id INT NOT NULL, commissionable_asset_id INT NOT NULL, PRIMARY KEY(mission_id, commissionable_asset_id))');
        $this->addSql('CREATE INDEX IDX_D40997EFBE6CAE90 ON mission_commissionable_asset (mission_id)');
        $this->addSql('CREATE INDEX IDX_D40997EF6C56C7E5 ON mission_commissionable_asset (commissionable_asset_id)');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23C32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23CC54C8C93 FOREIGN KEY (type_id) REFERENCES mission_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_user ADD CONSTRAINT FK_A4D17A46BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_user ADD CONSTRAINT FK_A4D17A46A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_commissionable_asset ADD CONSTRAINT FK_D40997EFBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_commissionable_asset ADD CONSTRAINT FK_D40997EF6C56C7E5 FOREIGN KEY (commissionable_asset_id) REFERENCES commissionable_asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE mission_user DROP CONSTRAINT FK_A4D17A46BE6CAE90');
        $this->addSql('ALTER TABLE mission_commissionable_asset DROP CONSTRAINT FK_D40997EFBE6CAE90');
        $this->addSql('DROP SEQUENCE mission_id_seq CASCADE');
        $this->addSql('DROP TABLE mission');
        $this->addSql('DROP TABLE mission_user');
        $this->addSql('DROP TABLE mission_commissionable_asset');
    }
}
