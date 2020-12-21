<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Type\PreAddAssetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/preAdd", name="app_organization_commissionable_pre_add_asset", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', organization)")
 */
class PreAddAssetController extends AbstractController
{
    public function __invoke(Organization $organization): Response
    {
        $form = $this->createForm(
            PreAddAssetType::class,
            ['organizationId' => $organization->id],
            ['organization' => $organization]
        )->createView();

        return $this->render('organization/commissionable_asset/preAdd.html.twig', [
            'form' => $form,
            'organization' => $organization,
        ]);
    }
}
