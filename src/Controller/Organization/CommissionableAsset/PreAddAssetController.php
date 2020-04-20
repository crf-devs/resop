<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

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
        return $this->render('organization/commissionable_asset/preAdd.html.twig', [
            'form' => $this->createForm(PreAddAssetType::class)->createView(),
        ]);
    }
}
