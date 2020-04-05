<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200405080155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove useless Organisation.name index';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX organisation_name_unique');
        $this->addSql('CREATE INDEX organization_name_idx ON organization (name)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX organization_name_idx');
        $this->addSql('CREATE UNIQUE INDEX organisation_name_unique ON organization (name)');
    }
}
