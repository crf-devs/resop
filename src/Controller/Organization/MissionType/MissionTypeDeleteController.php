<?php

declare(strict_types=1);

namespace App\Controller\Organization\MissionType;

use App\Entity\MissionType;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/delete/{id}", name="app_organization_mission_type_delete", methods={"GET"})
 */
class MissionTypeDeleteController extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, MissionType $missionType): RedirectResponse
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization || false === $organization->isParent()) {
            throw new AccessDeniedException();
        }

        $entityManager->beginTransaction();
        $entityManager->remove($missionType);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Le type de mission a été supprimé avec succès.');

        return $this->redirectToRoute('app_organization_mission_type_index');
    }
}
