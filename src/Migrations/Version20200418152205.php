<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200418152205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add availability status comment';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_availability ADD comment TEXT DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE commissionable_asset_availability ADD comment TEXT DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_availability DROP comment');
        $this->addSql('ALTER TABLE commissionable_asset_availability DROP comment');
    }
}
