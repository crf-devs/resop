<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200409125719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add search indexes on user and commissionable_asset tables.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX user_first_name_lower_idx ON users (LOWER(first_name))');
        $this->addSql('CREATE INDEX user_last_name_lower_idx ON users (LOWER(last_name))');
        $this->addSql('CREATE INDEX user_email_address_lower_idx ON users (LOWER(email_address))');
        $this->addSql('CREATE INDEX user_identification_number_lower_idx ON users (LOWER(identification_number))');
        $this->addSql('CREATE INDEX commissionable_asset_name_lower_idx ON commissionable_asset (LOWER(name))');
        $this->addSql('CREATE INDEX commissionable_asset_contact_lower_idx ON commissionable_asset (LOWER(contact))');
        $this->addSql('CREATE INDEX organization_name_lower_idx ON organization (LOWER(name))');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX user_first_name_lower_idx');
        $this->addSql('DROP INDEX user_last_name_lower_idx');
        $this->addSql('DROP INDEX user_email_address_lower_idx');
        $this->addSql('DROP INDEX user_identification_number_lower_idx');
        $this->addSql('DROP INDEX commissionable_asset_name_lower_idx');
        $this->addSql('DROP INDEX commissionable_asset_contact_lower_idx');
        $this->addSql('DROP INDEX organization_name_lower_idx');
    }
}
