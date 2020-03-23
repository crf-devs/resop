<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200323154736 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commissionable_asset_availability DROP CONSTRAINT FK_202CCBE131127A6');
        $this->addSql('ALTER TABLE commissionable_asset_availability ADD CONSTRAINT FK_202CCBE131127A6 FOREIGN KEY (planning_agent_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_availability DROP CONSTRAINT FK_BF7BDEBD31127A6');
        $this->addSql('ALTER TABLE user_availability ADD CONSTRAINT FK_BF7BDEBD31127A6 FOREIGN KEY (planning_agent_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_availability DROP CONSTRAINT fk_bf7bdebd31127a6');
        $this->addSql('ALTER TABLE user_availability ADD CONSTRAINT fk_bf7bdebd31127a6 FOREIGN KEY (planning_agent_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commissionable_asset_availability DROP CONSTRAINT fk_202ccbe131127a6');
        $this->addSql('ALTER TABLE commissionable_asset_availability ADD CONSTRAINT fk_202ccbe131127a6 FOREIGN KEY (planning_agent_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
