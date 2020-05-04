<?php

declare(strict_types=1);

namespace App\Controller\Organization\MissionType;

use App\Entity\MissionType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mission_type/{id}/delete", name="app_organization_mission_type_delete", methods={"GET"})
 * @Security("missionType.organization == user")
 */
class MissionTypeDeleteController extends AbstractController
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function __invoke(MissionType $missionType): RedirectResponse
    {
        $this->entityManager->remove($missionType);
        $this->entityManager->flush();

        $this->addFlash('success', 'organization.missionType.deleteSuccessMessage');

        return $this->redirectToRoute('app_organization_mission_type_index');
    }
}
