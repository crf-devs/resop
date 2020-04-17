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
 * @Route("mission_type/delete/{id}", name="app_organization_mission_type_delete", methods={"GET"})
 * @Security("missionType.organization == user")
 * @IsGranted("ROLE_PARENT_ORGANIZATION")
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
        /** @var Organization $this->getUser() */
        if (!$this->getUser() instanceof Organization || false === $this->getUser()->isParent()) {
            throw new AccessDeniedException();
        }

        $this->entityManager->remove($missionType);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('organization.mission_type.delete_success_message'));

        return $this->redirectToRoute('app_organization_mission_type_index');
    }
}
