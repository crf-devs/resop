<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Factory\OrganizationSelectorFormFactory;
use App\Repository\CommissionableAssetRepository;
use App\Security\Voter\OrganizationVoter;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_assets", methods={"GET"})
 * @IsGranted(OrganizationVoter::CAN_MANAGE, subject="organization")
 */
class AssetsListController extends AbstractController
{
    private CommissionableAssetRepository $assetRepository;
    private OrganizationSelectorFormFactory $organizationSelectorFormFactory;
    private PaginatorInterface $paginator;

    public function __construct(CommissionableAssetRepository $assetRepository, OrganizationSelectorFormFactory $organizationSelectorFormFactory, PaginatorInterface $paginator)
    {
        $this->assetRepository = $assetRepository;
        $this->organizationSelectorFormFactory = $organizationSelectorFormFactory;
        $this->paginator = $paginator;
    }

    public function __invoke(Request $request, Organization $organization): Response
    {
        /** @var Organization $currentOrganization */
        $currentOrganization = $this->getUser();
        $assets = $this->paginator->paginate(
            $this->assetRepository->findByOrganizationAndChildrenQb($organization),
            $request->query->getInt('page', 1),
            $this->getParameter('app.pagination_default_limit')
        );

        return $this->render(
            'organization/commissionable_asset/list.html.twig',
            [
                'organization' => $organization,
                'assets' => $assets,
                'organization_selector_form' => $this->organizationSelectorFormFactory->createForm(
                    $organization,
                    $currentOrganization
                )->createView(),
            ]
        );
    }
}
