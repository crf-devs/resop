<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\CommissionableAssetRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{organization<\d+>}/search", name="app_organization_search", methods={"GET"}, requirements={"id"="\d+"})
 * @Security("is_granted('ROLE_ORGANIZATION', organization)")
 */
final class SearchController extends AbstractOrganizationController
{
    public function __invoke(Request $request, Organization $organization, UserRepository $userRepository, CommissionableAssetRepository $commissionableAssetRepository, OrganizationRepository $organizationRepository): Response
    {
        /** @var string $query */
        $query = preg_replace('/\s+/', ' ', trim((string) $request->query->get('query')));
        if (empty($query)) {
            return $this->redirectToRoute('app_organization_dashboard');
        }

        return $this->render('organization/search.html.twig', [
            'organization' => $organization,
            'query' => $query,
            'users' => $userRepository->search($organization, $query),
            'assets' => $commissionableAssetRepository->search($organization, $query),
        ]);
    }
}
