<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\SkillSetDomain;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use MartinGeorgiev\Utils\DataStructure;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20200415085822 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription(): string
    {
        return 'Replace skill "nouveau" by "benevole" and add dependant skills to users.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $skillSetDomain = $this->container->get(SkillSetDomain::class);
        if (null === $skillSetDomain) {
            throw new \RuntimeException('Unable to find the SkillSet domain class');
        }

        // avoids annoying red message in dev when running migrations with a fresh database
        $nbUsers = (int) $this->connection->executeQuery('SELECT count(users) FROM users')->fetchOne();
        $this->skipIf(0 === $nbUsers, 'No users in database');

        $statement = $this->connection->executeQuery('SELECT id, skill_set as skill_set FROM users');

        while ($row = $statement->fetchAssociative()) {
            $skills = str_replace('nouveau', 'benevole', DataStructure::transformPostgresTextArrayToPHPArray($row['skill_set']));
            $skills = $skillSetDomain->getIncludedSkillsFromSkillSet($skills);

            $this->addSql(
                'UPDATE users SET skill_set = :skill_set WHERE id = :id',
                [
                    ':id' => $row['id'],
                    ':skill_set' => DataStructure::transformPHPArrayToPostgresTextArray($skills),
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // cannot guess which skill was added
    }
}
