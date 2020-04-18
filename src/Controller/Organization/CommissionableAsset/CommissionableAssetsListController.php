<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Factory\OrganizationSelectorFormFactory;
use App\Repository\CommissionableAssetRepository;
use App\Security\Voter\OrganizationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/commissionable-assets/{id}", name="app_organization_commissionable_assets", methods={"GET"})
 * @IsGranted(OrganizationVoter::CAN_LIST_ASSETS, subject="organization")
 */
class CommissionableAssetsListController extends AbstractController
{
    private CommissionableAssetRepository $assetRepository;
    private OrganizationSelectorFormFactory $organizationSelectorFormFactory;

    public function __construct(CommissionableAssetRepository $assetRepository, OrganizationSelectorFormFactory $organizationSelectorFormFactory)
    {
        $this->assetRepository = $assetRepository;
        $this->organizationSelectorFormFactory = $organizationSelectorFormFactory;
    }

    public function __invoke(Request $request, Organization $organization): Response
    {
        /** @var Organization $currentOrganization */
        $currentOrganization = $this->getUser();

        return $this->render(
            'organization/commissionable_asset/list.html.twig',
            [
                'organization' => $organization,
                'assets' => $this->assetRepository->findByOrganization($organization),
                'organization_selector_form' => $this->organizationSelectorFormFactory->createForm(
                    $organization,
                    $currentOrganization,
                    $request->attributes->get('_route')
                )->createView(),
            ]
        );
    }
}
