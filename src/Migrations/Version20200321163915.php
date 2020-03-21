<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200321163915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE organization_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organization (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "users" (id INT NOT NULL, organization_id INT DEFAULT NULL, identification_number VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, occupation VARCHAR(255) NOT NULL, organization_occupation VARCHAR(255) DEFAULT NULL, skill_set JSON DEFAULT NULL, vulnerable BOOLEAN NOT NULL, fully_equipped BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D64932C8A3DE ON "users" (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX user_identification_number_unique ON "users" (identification_number)');
        $this->addSql('CREATE UNIQUE INDEX user_email_address_unique ON "users" (email_address)');
        $this->addSql('ALTER TABLE "users" ADD CONSTRAINT FK_8D93D64932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64932C8A3DE');
        $this->addSql('DROP SEQUENCE organization_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE "users"');
    }
}
