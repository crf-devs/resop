<?php

declare(strict_types=1);

namespace App\Controller\Organization\MissionType;

use App\Entity\MissionType;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/delete/{id}", name="app_organization_mission_type_delete", methods={"GET"})
 */
class MissionTypeDeleteController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

        $this->addFlash('success', $this->translator->trans('organization.mission_type.delete_success_message'));

        return $this->redirectToRoute('app_organization_mission_type_index');
    }
}
