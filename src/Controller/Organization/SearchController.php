<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Repository\CommissionableAssetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", name="app_organization_search", methods={"GET"})
 */
final class SearchController extends AbstractController
{
    public function __invoke(Request $request, UserRepository $userRepository, CommissionableAssetRepository $commissionableAssetRepository): Response
    {
        /** @var string $query */
        $query = preg_replace('/\s+/', ' ', trim($request->query->get('query')));
        if (empty($query)) {
            throw $this->createNotFoundException('Missing "query" query parameter');
        }

        return $this->render('organization/search.html.twig', [
            'query' => $query,
            'users' => $userRepository->search($query),
            'assets' => $commissionableAssetRepository->search($query),
        ]);
    }
}
