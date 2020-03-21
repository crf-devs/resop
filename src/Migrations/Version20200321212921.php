<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200321212921 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE user_availability_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_availability (id INT NOT NULL, user_id INT NOT NULL, planning_agent_id INT DEFAULT NULL, start_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, end_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, booked_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF7BDEBDA76ED395 ON user_availability (user_id)');
        $this->addSql('CREATE INDEX IDX_BF7BDEBD31127A6 ON user_availability (planning_agent_id)');
        $this->addSql('CREATE UNIQUE INDEX user_availability_slot_unique ON user_availability (user_id, start_time, end_time)');
        $this->addSql('COMMENT ON COLUMN user_availability.start_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_availability.end_time IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_availability.booked_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_availability.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_availability.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE user_availability ADD CONSTRAINT FK_BF7BDEBDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_availability ADD CONSTRAINT FK_BF7BDEBD31127A6 FOREIGN KEY (planning_agent_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_8d93d64932c8a3de RENAME TO IDX_1483A5E932C8A3DE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE user_availability_id_seq CASCADE');
        $this->addSql('DROP TABLE user_availability');
        $this->addSql('ALTER INDEX idx_1483a5e932c8a3de RENAME TO idx_8d93d64932c8a3de');
    }
}
