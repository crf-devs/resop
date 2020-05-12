<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200512102054 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Use dynamic configuration for users.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ADD properties JSON NOT NULL');
        $this->addSql('ALTER TABLE users DROP organization_occupation');
        $this->addSql('DROP INDEX user_fully_equipped_idx');
        $this->addSql('ALTER TABLE users DROP fully_equipped');
        $this->addSql('ALTER TABLE users DROP driving_licence');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ADD organization_occupation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users DROP properties');
        $this->addSql('ALTER TABLE users ADD fully_equipped BOOLEAN NOT NULL');
        $this->addSql('CREATE INDEX user_fully_equipped_idx ON users (fully_equipped)');
        $this->addSql('ALTER TABLE users ADD driving_licence BOOLEAN DEFAULT \'false\' NOT NULL');
    }
}
