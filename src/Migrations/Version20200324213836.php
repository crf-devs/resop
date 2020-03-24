<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200324213836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add order index';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX commissionable_asset_name_idx ON commissionable_asset (name)');
        $this->addSql('CREATE INDEX user_firstname_idx ON users (first_name)');
        $this->addSql('CREATE INDEX user_lastname_idx ON users (last_name)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX commissionable_asset_name_idx');
        $this->addSql('DROP INDEX user_firstname_idx');
        $this->addSql('DROP INDEX user_lastname_idx');
    }
}
