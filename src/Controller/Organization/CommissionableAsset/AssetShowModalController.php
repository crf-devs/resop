<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{asset<\d+>}/modal", name="app_organization_asset_show_modal", methods={"GET", "POST"})
 */
class AssetShowModalController extends AbstractController
{
    public function __invoke(CommissionableAsset $asset): Response
    {
        return $this->render('organization/commissionable_asset/show-modal-content.html.twig', ['asset' => $asset]);
    }
}
