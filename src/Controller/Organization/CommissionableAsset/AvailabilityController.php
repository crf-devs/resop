<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Domain\AvailabilitiesDomain;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use App\Form\Type\AvailabilitiesDomainType;
use App\Repository\CommissionableAssetAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{asset<\d+>}/availability/{week<\d{4}-W\d{2}>?}", name="app_organization_asset_availability", methods={"GET", "POST"})
 */
final class AvailabilityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commissionableAssetAvailabilityRepository = $commissionableAssetAvailabilityRepository;
    }

    public function __invoke(Request $request, CommissionableAsset $asset, string $slotInterval): Response
    {
        $week = $request->attributes->get('week');

        try {
            $start = new \DateTimeImmutable($week ?: 'monday this week');
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
        }

        $interval = $start->diff(new \DateTimeImmutable());
        // edit current week and next week only
        if ($interval->days > 6) {
            return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
        }

        $end = $start->add(new \DateInterval('P7D'));

        $availabilitiesDomain = AvailabilitiesDomain::generate(
            $start,
            $end,
            $slotInterval,
            $this->commissionableAssetAvailabilityRepository->findBetweenDates($asset, $start, $end)
        );

        $form = $this
            ->createForm(AvailabilitiesDomainType::class, $availabilitiesDomain)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitiesDomain->compute($this->entityManager, CommissionableAssetAvailability::class, $asset);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Les disponibilités du véhicule "%s" ont été mises à jour avec succès', $asset));

            return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
        }

        return $this->render(
            'organization/commissionable_asset/availability.html.twig',
            [
                'form' => $form->createView(),
                'asset' => $asset,
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
