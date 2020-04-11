<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200411165144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add mission types';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE mission_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mission_type (id INT NOT NULL, organization_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, user_skills_requirement JSON NOT NULL, asset_types_requirement JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A59CFB2632C8A3DE ON mission_type (organization_id)');
        $this->addSql('ALTER TABLE mission_type ADD CONSTRAINT FK_A59CFB2632C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE mission_type_id_seq CASCADE');
        $this->addSql('DROP TABLE mission_type');
    }
}
