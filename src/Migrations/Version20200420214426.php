<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200420214426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add assetType and related changes + data migration';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE asset_type_id_seq INCREMENT BY 1');
        $this->addSql('CREATE TABLE asset_type (id INT NOT NULL, organization_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, properties JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68BA92E132C8A3DE ON asset_type (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX assetType_unique_org_name ON asset_type (organization_id, name)');
        $this->addSql('ALTER TABLE asset_type ADD CONSTRAINT FK_68BA92E132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE commissionable_asset ADD properties JSON');
        $this->addSql('ALTER TABLE commissionable_asset ADD asset_type_id INT');
        $this->addSql('ALTER TABLE commissionable_asset ADD CONSTRAINT FK_4D68E555A6A2CDC5 FOREIGN KEY (asset_type_id) REFERENCES asset_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4D68E555A6A2CDC5 ON commissionable_asset (asset_type_id)');

        $this->migrateData();

        $this->addSql('SELECT setval(\'asset_type_id_seq\', 3, true)');

        $this->addSql('ALTER TABLE commissionable_asset ALTER COLUMN properties SET NOT NULL');
        $this->addSql('ALTER TABLE commissionable_asset ALTER COLUMN asset_type_id SET NOT NULL');
        $this->addSql('ALTER TABLE commissionable_asset DROP COLUMN type, DROP COLUMN has_mobile_radio, DROP COLUMN has_first_aid_kit, DROP COLUMN parking_location, DROP COLUMN seating_capacity, DROP COLUMN contact, DROP COLUMN comments, DROP COLUMN license_plate');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function migrateData(): void
    {
        // avoids annoying red message in dev when running migrations with a fresh database
        $nbUsers = (int) $this->connection->executeQuery('SELECT count(users) FROM users')->fetchOne();
        if (0 === $nbUsers) {
            return;
        }

        // migration

        $this->addSql('INSERT INTO "asset_type" ("id", "organization_id", "name", "properties") VALUES
(1,	1,	\'VL\',	\'[{"key":"radio","type":"boolean","label":"Pr\u00e9sence d\'\'un mobile radio ?","help":"","required":true,"hidden":false},{"key":"kitSecours","type":"boolean","label":"Pr\u00e9sence d\'\'un lot de secours ?","help":"","required":true,"hidden":false},{"key":"stationnement","type":"smallText","label":"Lieu de stationnement","help":"","required":false,"hidden":false},{"key":"contact","type":"smallText","label":"Qui contacter ?","help":"","required":false,"hidden":false},{"key":"place","type":"number","label":"Combien de places ?","help":"","required":false,"hidden":false},{"key":"immatriculation","type":"smallText","label":"Plaque d\'\'immatriculation","help":"","required":false,"hidden":false},{"key":"commentaire","type":"text","label":"Commentaires","help":"","required":false,"hidden":false}]\'),
(2,	1,	\'VPSP\',	\'[{"key":"stationnement","type":"smallText","label":"Lieu de stationnement","help":"","required":false,"hidden":false},{"key":"contact","type":"smallText","label":"Qui contacter ?","help":"","required":false,"hidden":false},{"key":"immatriculation","type":"smallText","label":"Plaque d\'\'immatriculation","help":"","required":false,"hidden":false},{"key":"commentaire","type":"text","label":"Commentaires","help":"","required":false,"hidden":false}]\')');

        $statement = $this->connection->executeQuery('SELECT id, type, has_mobile_radio, has_first_aid_kit, parking_location, contact, seating_capacity, license_plate, comments  FROM commissionable_asset');

        while ($row = $statement->fetchAssociative()) {
            $properties = [
                'stationnement' => !empty($row['parking_location']) ? $row['parking_location'] : '',
                'places' => !empty($row['seating_capacity']) ? $row['seating_capacity'] : '',
                'contact' => !empty($row['contact']) ? $row['contact'] : '',
                'immatriculation' => !empty($row['license_plate']) ? $row['license_plate'] : '',
                'commentaires' => !empty($row['comments']) ? $row['comments'] : '',
            ];
            $assetTypeId = 2;

            if ('VL' === $row['type']) {
                $assetTypeId = 1;
                $properties += [
                    'radio' => !empty($row['has_mobile_radio']),
                    'kitSecours' => !empty($row['has_first_aid_kit']),
                ];
            }

            $this->addSql(
                'UPDATE commissionable_asset SET properties = :properties, asset_type_id = :asset_type_id  WHERE id = :id',
                [
                    ':id' => $row['id'],
                    ':properties' => json_encode($properties),
                    ':asset_type_id' => $assetTypeId,
                ]
            );
        }

        // migration mission types
        $statement = $this->connection->executeQuery('SELECT id, asset_types_requirement  FROM mission_type');
        while ($row = $statement->fetchAssociative()) {
            $requirements = json_decode($row['asset_types_requirement'], true);
            $countRequirements = \count($requirements);
            if (0 === $countRequirements) {
                continue;
            }

            foreach ($requirements as $i => $iValue) {
                $current = $iValue;
                $current['type'] = 'VL' === $current['type'] ? 1 : 2;

                $requirements[$i] = $current;
            }

            $this->addSql(
                'UPDATE mission_type SET asset_types_requirement = :asset_types_requirement WHERE id = :id',
                [
                    ':id' => $row['id'],
                    ':asset_types_requirement' => json_encode($requirements),
                ]
            );
        }
    }
}
