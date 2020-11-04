<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Factory\OrganizationSelectorFormFactory;
use App\Repository\CommissionableAssetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_assets", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', organization)")
 */
class AssetsListController extends AbstractController
{
    private CommissionableAssetRepository $assetRepository;
    private OrganizationSelectorFormFactory $organizationSelectorFormFactory;

    public function __construct(CommissionableAssetRepository $assetRepository, OrganizationSelectorFormFactory $organizationSelectorFormFactory)
    {
        $this->assetRepository = $assetRepository;
        $this->organizationSelectorFormFactory = $organizationSelectorFormFactory;
    }

    public function __invoke(Request $request, Organization $organization, Organization $currentOrganization): Response
    {
        return $this->render(
            'organization/commissionable_asset/list.html.twig',
            [
                'organization' => $organization,
                'assets' => $this->assetRepository->findByOrganization($organization),
                'organization_selector_form' => $this->organizationSelectorFormFactory->createForm(
                    $organization,
                    $currentOrganization
                )->createView(),
            ]
        );
    }
}
