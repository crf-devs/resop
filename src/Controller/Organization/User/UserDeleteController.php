<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\UserAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{organization}/users/{userToDelete}/delete", name="app_user_delete", methods={"GET"})
 * @Security("userToDelete.organization.id == organization")
 */
class UserDeleteController extends AbstractController
{
    private UserAvailabilityRepository $userAvailabilityRepository;

    public function __construct(UserAvailabilityRepository $userAvailabilityRepository)
    {
        $this->userAvailabilityRepository = $userAvailabilityRepository;
    }

    public function __invoke(EntityManagerInterface $entityManager, User $userToDelete): RedirectResponse
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization || false === $organization->isParent()) {
            throw new AccessDeniedException();
        }

        $entityManager->beginTransaction();
        $this->userAvailabilityRepository->deleteByOwner($userToDelete);
        $entityManager->remove($userToDelete);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Le bénévole a été supprimé avec succès.');

        return $this->redirectToRoute('app_organization_user_list', ['organization' => $organization->id]);
    }
}
