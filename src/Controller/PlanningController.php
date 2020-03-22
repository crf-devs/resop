<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use App\Entity\User;
use App\Entity\UserAvailability;
use App\Form\Type\PlanningSearchType;
use App\Repository\AvailabilityRepositoryInterface;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private CommissionableAssetRepository $assetRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CommissionableAssetRepository $assetRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @Route("/planning", name="planning", methods={"GET", "POST"})
     */
    public function planning(Request $request): Response
    {
        $form = $this->createForm(PlanningSearchType::class);
        $form->handleRequest($request);

        $from = $form->get('from')->getData();
        $to = $form->get('to')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            [$users, $assets] = $this->searchEntities($form->getData());
            $usersAvailabilities = $this->prepareAvailabilities($users, $from, $to);
            $assetsAvailabilities = $this->prepareAvailabilities($assets, $from, $to);
        }

        return $this->render('organization/planning.html.twig', [
            'form' => $form->createView(),
            'from' => $from,
            'to' => $to,
            'usersAvailabilities' => $usersAvailabilities ?? [],
            'assetsAvailabilities' => $assetsAvailabilities ?? [],
        ]);
    }

    private function searchEntities(array $formData): array
    {
        $organizations = $formData['organizations'] instanceof ArrayCollection ? $formData['organizations']->toArray() : [];

        $users = $formData['volunteer'] ? $this->userRepository->findBySkillsAndEquippedAndVulnerableAndOrganizations(
            $formData['volunteerSkills'],
            $formData['volunteerEquipped'],
            !$formData['volunteerHideVulnerable'],
            $organizations
        ) : [];

        $assets = $formData['asset'] ? $this->assetRepository->findByTypesAndOrganizations($formData['assetTypes'], $organizations) : [];

        return [$users, $assets];
    }

    private function prepareAvailabilities(iterable $availabilitables, DateTimeInterface $from, DateTimeInterface $to): array
    {
        $availabilityRepository = $this->getAvailabilityRepository($availabilitables[0] ?? null);

        $result = [];
        foreach ($availabilitables as $availabilitable) {
            $fromIterator = $from instanceof DateTimeImmutable ? $from : DateTimeImmutable::createFromMutable($from);
            $intervalAvailabilities = [];

            while ($fromIterator < $to) {
                $intervalAvailability = $availabilityRepository ? $availabilityRepository->findOneByInterval($from, $to) : null;

                $intervalAvailabilities[] = [
                    'from' => $fromIterator,
                    'to' => $fromIterator->add(new \DateInterval('PT2H')),
                    'status' => $intervalAvailability ? $intervalAvailability->status : AvailabilityInterface::STATUS_LOCKED,
                ];

                $fromIterator = $fromIterator->add(new \DateInterval('PT2H'));
            }

            $result[] = [
                'entity' => $availabilitable,
                'availabilities' => $intervalAvailabilities,
            ];
        }

        return $result;
    }

    private function getAvailabilityRepository($availabitable): ?AvailabilityRepositoryInterface
    {
        if ($availabitable instanceof User) {
            return $this->em->getRepository(UserAvailability::class);
        }

        if ($availabitable instanceof CommissionableAsset) {
            return $this->em->getRepository(CommissionableAssetAvailability::class);
        }

        return null;
    }
}
