<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200322162455 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users DROP skill_set');
        $this->addSql('ALTER TABLE users ADD skill_set text[] DEFAULT NULL');
        $this->addSql('CREATE INDEX user_skill_set_idx ON users (skill_set)');
        $this->addSql('CREATE INDEX user_vulnerable_idx ON users (vulnerable)');
        $this->addSql('CREATE INDEX user_fully_equipped_idx ON users (fully_equipped)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
