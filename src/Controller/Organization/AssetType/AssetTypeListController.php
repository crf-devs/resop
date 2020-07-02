<?php

declare(strict_types=1);

namespace App\Controller\Organization\AssetType;

use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_assetType_list", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION')")
 */
class AssetTypeListController extends AbstractController
{
    private AssetTypeRepository $assetTypeRepository;
    private PaginatorInterface $paginator;

    public function __construct(AssetTypeRepository $assetTypeRepository, PaginatorInterface $paginator)
    {
        $this->assetTypeRepository = $assetTypeRepository;
        $this->paginator = $paginator;
    }

    public function __invoke(Request $request): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();
        $assetTypes = $this->paginator->paginate(
            $this->assetTypeRepository->findByOrganization($organization),
            $request->query->getInt('page', 1),
            $this->getParameter('app.pagination_default_limit')
        );

        return $this->render('organization/assetType/list.html.twig', [
            'assetTypes' => $assetTypes,
        ]);
    }
}
