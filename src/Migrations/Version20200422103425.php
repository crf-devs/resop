<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200422103425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use libphonenumber\PhoneNumber on User.phoneNumber';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ALTER phone_number TYPE VARCHAR(35)');
        $this->addSql('ALTER TABLE users ALTER phone_number DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN users.phone_number IS \'(DC2Type:phone_number)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ALTER phone_number TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER phone_number SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN users.phone_number IS NULL');
    }
}
