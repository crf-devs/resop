<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\Organization;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{item<\d+>}/promote", name="app_organization_user_promote", methods={"GET"}, defaults={"promote"=true})
 * @Route("/{item<\d+>}/revoke", name="app_organization_user_revoke", methods={"GET"}, defaults={"promote"=false})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', item.organization) and user !== item")
 */
class PromoteRevokeController extends AbstractOrganizationController
{
    /**
     * @param EntityManager $entityManager
     */
    public function __invoke(EntityManagerInterface $entityManager, Organization $organization, User $item, bool $promote): Response
    {
        if ($promote) {
            $item->addManagedOrganization($organization);
            $this->addFlash('success', sprintf('L\'utilisateur "%s" a été promu administrateur de "%s" avec succès.', $item->getFullName(), $organization->getName()));
        } else {
            $item->removeManagedOrganization($organization);
            $this->addFlash('success', sprintf('Le privilège d\'administrateur pour la structure "%s" de "%s" a été révoquée avec succès.', $organization->getName(), $item->getFullName()));
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_organization_user_list', ['organization' => $item->getNotNullOrganization()->id]);
    }
}
