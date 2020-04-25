<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\Organization;
use App\Form\Type\PreAddAssetType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("preAdd",  name="app_organization_commissionable_pre_add_asset" , methods={"GET", "POST"})
 */
class PreAddAssetController extends AbstractController
{
    public function __invoke(): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        $form = $this->createForm(
            PreAddAssetType::class,
            null,
            ['parent_organization' => $organization->getParentOrganization()])
            ->createView();

        return $this->render('organization/commissionable_asset/preAdd.html.twig', [
            'form' => $form,
        ]);
    }
}
