<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200512102054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use dynamic configuration for users.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ADD properties JSON');
        $this->addSql(
            "UPDATE users set properties = json_build_object(
                'organizationOccupation', organization_occupation,
                'fullyEquipped', fully_equipped,
                'drivingLicence', driving_licence,
                'vulnerable', vulnerable,
                'occupation', occupation
            )"
        );
        $this->addSql('ALTER TABLE users alter column properties set not null;');

        $this->addSql('ALTER TABLE users DROP organization_occupation');
        $this->addSql('DROP INDEX user_fully_equipped_idx');
        $this->addSql('ALTER TABLE users DROP fully_equipped');
        $this->addSql('ALTER TABLE users DROP driving_licence');
        $this->addSql('DROP INDEX user_vulnerable_idx');
        $this->addSql('ALTER TABLE users DROP vulnerable');
        $this->addSql('ALTER TABLE users DROP occupation');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');
        $this->throwIrreversibleMigrationException();
    }
}
