<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200516150857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Organization.password, add User.password and User.roles, and add Organizations.admins relation.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE user_organization (user_id INT NOT NULL, organization_id INT NOT NULL, PRIMARY KEY(user_id, organization_id))');
        $this->addSql('CREATE INDEX IDX_41221F7EA76ED395 ON user_organization (user_id)');
        $this->addSql('CREATE INDEX IDX_41221F7E32C8A3DE ON user_organization (organization_id)');
        $this->addSql('ALTER TABLE user_organization ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_organization ADD CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD roles TEXT NOT NULL DEFAULT \'a:1:{i:0;s:14:"ROLE_VOLUNTEER";}\'');
        $this->addSql('ALTER TABLE users ALTER roles DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN users.roles IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE organization DROP password');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE user_organization');
        $this->addSql('ALTER TABLE users DROP password');
        $this->addSql('ALTER TABLE users DROP roles');
        $this->addSql('ALTER TABLE organization ADD password VARCHAR(255) DEFAULT NULL');
    }
}
