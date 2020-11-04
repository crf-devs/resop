<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\CommissionableAssetRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * {organization} parameter is useless for the moment, but will be useful in ticket https://github.com/crf-devs/resop/issues/338
 *
 * @Route("/{organization<\d+>}/search", name="app_organization_search", methods={"GET"}, requirements={"id"="\d+"})
 */
final class SearchController extends AbstractOrganizationController
{
    public function __invoke(Request $request, UserRepository $userRepository, CommissionableAssetRepository $commissionableAssetRepository, OrganizationRepository $organizationRepository): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        /** @var string $query */
        $query = preg_replace('/\s+/', ' ', trim((string) $request->query->get('query')));
        if (empty($query)) {
            return $this->redirectToRoute('app_organization_dashboard');
        }

        return $this->render('organization/search.html.twig', [
            'query' => $query,
            'users' => $userRepository->search($organization, $query),
            'assets' => $commissionableAssetRepository->search($organization, $query),
        ]);
    }
}
