<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Domain\AvailabilitiesDomain;
use App\Entity\CommissionableAssetAvailability;
use App\Form\Type\AvailabilitiesDomainType;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{id<\d+>}/availability/{week<\d{4}-W\d{2}>?}", name="organization_commisionable_asset_availability", methods={"GET", "POST"})
 */
final class AvailabilityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository;
    private CommissionableAssetRepository $commissionableAssetRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository,
        CommissionableAssetRepository $commissionableAssetRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commissionableAssetAvailabilityRepository = $commissionableAssetAvailabilityRepository;
        $this->commissionableAssetRepository = $commissionableAssetRepository;
    }

    public function __invoke(Request $request): Response
    {
        $asset = $this->commissionableAssetRepository->findOneBy([
            'id' => $request->attributes->get('id'),
        ]);

        if (null === $asset) {
            throw $this->createAccessDeniedException();
        }

        $week = $request->attributes->get('week');

        try {
            $start = new \DateTimeImmutable($week ?: 'monday this week');
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_organization_commissionable_assets');
        }

        $interval = $start->diff(new \DateTimeImmutable());
        // edit current week and next week only
        if ($interval->days > 6) {
            return $this->redirectToRoute('app_organization_commissionable_assets');
        }

        $end = $start->add(new \DateInterval('P7D'));

        $availabilitiesDomain = AvailabilitiesDomain::generate(
            $start,
            $end,
            $this->commissionableAssetAvailabilityRepository->findBetweenDates($asset, $start, $end)
        );

        $form = $this
            ->createForm(AvailabilitiesDomainType::class, $availabilitiesDomain)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitiesDomain->compute($this->entityManager, CommissionableAssetAvailability::class, $asset);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Les disponibilités du véhicule "%s" ont été mises à jour avec succès', $asset));

            return $this->redirectToRoute('app_organization_commissionable_assets');
        }

        return $this->render('organization/commissionable_asset/availability.html.twig', [
            'form' => $form->createView(),
            'asset' => $asset,
        ]);
    }
}
