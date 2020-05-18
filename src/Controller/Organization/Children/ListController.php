<?php

declare(strict_types=1);

namespace App\Controller\Organization\Children;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route(name="app_organization_list", methods={"GET"})
 */
class ListController extends AbstractController
{
    protected OrganizationRepository $organizationRepository;
    private PaginatorInterface $paginator;

    public function __construct(OrganizationRepository $organizationRepository, PaginatorInterface $paginator)
    {
        $this->organizationRepository = $organizationRepository;
        $this->paginator = $paginator;
    }

    public function __invoke(Request $request): Response
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization || !$organization->isParent()) {
            throw new AccessDeniedException();
        }

        $organizations = $this->paginator->paginate(
            $this->organizationRepository->findChildrenQueryBuilder($organization),
            $request->query->getInt('page', 1),
            $this->getParameter('app.pagination_default_limit')
        );

        return $this->render('organization/list.html.twig', [
            'organizations' => $organizations,
        ]);
    }
}
