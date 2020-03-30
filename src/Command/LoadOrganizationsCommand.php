<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class LoadOrganizationsCommand extends Command
{
    protected static $defaultName = 'app:load-organizations';

    private const ORGANIZATIONS = [
        'DT75' => [
            'UL 01-02',
            'UL 03-10',
            'UL 04',
            'UL 05',
            'UL 06',
            'UL 07',
            'UL 08',
            'UL 09',
            'UL 11',
            'UL 12',
            'UL 13',
            'UL 14',
            'UL 15',
            'UL 16',
            'UL 17',
            'UL 18',
            'UL 19',
            'UL 20',
        ],
    ];

    private EntityManagerInterface $entityManager;

    private EncoderFactoryInterface $encoders;

    private array $outputTable = [];

    public function __construct(EntityManagerInterface $entityManager, EncoderFactoryInterface $encoders)
    {
        $this->entityManager = $entityManager;
        $this->encoders = $encoders;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oldOrganizations = $this->getIndexedOrganizations();

        foreach (self::ORGANIZATIONS as $parentOrganizationName => $childrenOrganizations) {
            if ($this->isNewOrganization($oldOrganizations, $parentOrganizationName)) {
                $parentOrganization = $this->createOrganization($parentOrganizationName);
            } else {
                $parentOrganization = $oldOrganizations[$parentOrganizationName];
            }

            foreach ($childrenOrganizations as $childOrganizationName) {
                if ($this->isNewOrganization($oldOrganizations, $childOrganizationName, $parentOrganizationName)) {
                    $this->createOrganization($childOrganizationName, $parentOrganization);
                }
            }
        }

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->table(['Organization', 'Password'], $this->outputTable);

        return 0;
    }

    private function getIndexedOrganizations(): array
    {
        $organizations = $this->entityManager->getRepository(Organization::class)->findAllWithParent();

        $indexedOrganizations = [];
        foreach ($organizations as $organization) {
            $indexedOrganizations[$organization->getName()] = $organization;
        }

        return $indexedOrganizations;
    }

    /**
     * @param Organization[] $oldOrganizations
     */
    private function isNewOrganization(
        array $oldOrganizations,
        string $newOrganizationName,
        string $newOrganizationParentName = null
    ): bool {
        foreach ($oldOrganizations as $oldOrganization) {
            if ($oldOrganization->getName() === $newOrganizationName
             && $oldOrganization->getParentName() === $newOrganizationParentName) {
                return false;
            }
        }

        return true;
    }

    private function createOrganization(string $organizationName, Organization $parentOrganization = null): Organization
    {
        $organization = new Organization(null, $organizationName, $parentOrganization);

        $encoder = $this->encoders->getEncoder(Organization::class);
        $plainPassword = $this->getPlainPassword();
        $organization->setPassword($encoder->encodePassword($plainPassword, null));

        $this->entityManager->persist($organization);

        $this->outputTable[] = [$organization->getName(), $plainPassword];

        return $organization;
    }

    private function getPlainPassword(): string
    {
        return $plainPassword = substr(sha1(random_bytes(6)), 0, 6);
    }
}
