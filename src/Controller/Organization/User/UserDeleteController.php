<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\User;
use App\Repository\UserAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{user<\d+>}/delete", name="app_organization_user_delete", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', user.organization)")
 */
class UserDeleteController extends AbstractOrganizationController
{
    private UserAvailabilityRepository $userAvailabilityRepository;

    public function __construct(UserAvailabilityRepository $userAvailabilityRepository)
    {
        $this->userAvailabilityRepository = $userAvailabilityRepository;
    }

    public function __invoke(EntityManagerInterface $entityManager, User $user): RedirectResponse
    {
        $entityManager->beginTransaction();
        $this->userAvailabilityRepository->deleteByOwner($user);
        $entityManager->remove($user);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Le bénévole a été supprimé avec succès.');

        return $this->redirectToRoute('app_organization_user_list', ['organization' => $user->getNotNullOrganization()->id]);
    }
}
