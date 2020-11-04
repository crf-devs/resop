<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{user<\d+>}/modal", name="app_organization_user_show_modal", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', user.organization)")
 */
class UserShowModalController extends AbstractController
{
    public function __invoke(User $user): Response
    {
        return $this->render('organization/user/show-modal-content.html.twig', [
            'user' => $user,
        ]);
    }
}
