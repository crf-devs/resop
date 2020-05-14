<?php

declare(strict_types=1);

namespace App\Controller\Organization\Children;

use App\Entity\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_list", methods={"GET"})
 * @Security("organization.isParent()")
 */
class ListController extends AbstractController
{
    public function __invoke(Organization $organization): Response
    {
        return $this->render('organization/list.html.twig', [
            'organization' => $organization,
        ]);
    }
}
