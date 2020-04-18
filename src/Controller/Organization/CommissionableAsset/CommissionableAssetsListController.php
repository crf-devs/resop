<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Factory\OrganizationSelectorFormFactory;
use App\Repository\CommissionableAssetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommissionableAssetsListController extends AbstractController
{
    private CommissionableAssetRepository $assetRepository;
    private OrganizationSelectorFormFactory $organizationSelectorFormFactory;

    public function __construct(CommissionableAssetRepository $assetRepository, OrganizationSelectorFormFactory $organizationSelectorFormFactory)
    {
        $this->assetRepository = $assetRepository;
        $this->organizationSelectorFormFactory = $organizationSelectorFormFactory;
    }

    /**
     * @Route("/{id}", name="app_organization_commissionable_assets", methods={"GET"})
     */
    public function __invoke(Request $request, Organization $organization): Response
    {
        $currentOrganization = $this->getUser();
        if (!$currentOrganization instanceof Organization) {
            throw new AccessDeniedException();
        }

        if ($currentOrganization !== $organization && $organization->parent !== $currentOrganization) {
            throw new AccessDeniedException('You cannot manage this organization');
        }

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
