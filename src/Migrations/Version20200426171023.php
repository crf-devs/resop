<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200426171023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update users phones';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE users SET phone_number = CONCAT(\'+33\', SUBSTRING(phone_number,2)) WHERE phone_number LIKE \'0%\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE users SET phone_number = CONCAT(\'0\', SUBSTRING(phone_number,4)) WHERE phone_number LIKE \'+33%\'');
    }
}
